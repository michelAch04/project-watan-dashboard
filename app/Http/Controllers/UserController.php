<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Zone;
use App\Models\City;
use App\Models\PwMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule; // Added Rule for complex validation
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Get the authenticated user's zone (for HORs)
     * OPTIMIZATION: Static caching to prevent multiple DB calls in one request
     */
    private function getUserZone()
    {
        static $zone = null;
        if ($zone != null) return $zone;

        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return $zone = null; // Admin can see all zones
        }

        if ($user->hasRole('hor')) {
            return $zone = $user->zones()->first();
        }

        return $zone = null;
    }

    /**
     * Check if user is admin
     */
    private function isAdmin()
    {
        return Auth::user()->hasRole('admin');
    }

    /**
     * Check if user can manage a specific user based on zone restrictions
     * OPTIMIZATION: Replaced PHP loop with Database 'Exists' query
     */
    private function canManageUser($targetUser)
    {
        if ($this->isAdmin()) {
            return true;
        }

        $horZone = $this->getUserZone();
        if (!$horZone) {
            return false;
        }

        // Direct report check
        if($targetUser->manager_id == Auth::id()) {
            return true;
        }

        // 1. Zone Check
        $userZone = $targetUser->zones->first();
        if ($userZone && $userZone->id == $horZone->id) {
            return true;
        }

        // 2. City Check (Optimized)
        $hasCityInZone = City::notCancelled()
            ->whereJsonContains('user_id', (string)$targetUser->id)
            ->where('zone_id', $horZone->id)
            ->exists();

        if ($hasCityInZone) {
            return true;
        }

        // 3. PW Member's voter location check (for newly created users without assigned location)
        $voterZoneId = $targetUser->pwMember?->voter?->city?->zone_id;
        if ($voterZoneId && $voterZoneId == $horZone->id) {
            return true;
        }

        return false;
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::notCancelled()->with(['roles', 'manager', 'zones', 'pwMember']);

        $horZone = $this->getUserZone();

        // 1. Access Control Logic
        if ($horZone) {
            $zoneId = $horZone->id;
            
            $query->where(function($q) use ($zoneId) {
                $q->whereHas('zones', function($q) use ($zoneId) {
                    $q->where('id', $zoneId);
                })
                ->orWhere(function($sq) use ($zoneId) {
                    $sq->hasCityInZone($zoneId);
                });
            });
        }
        else if(!($this->isAdmin() || $horZone)) {
            return view('users.index', [
                'users' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25), 
                'zones' => []
            ]);
        }

        // 2. Search Functionality
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "{$search}%")
                  ->orWhere('email', 'like', "{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // 3. Zone Filter
        if ($request->filled('zone_id')) {
            $zoneId = $request->zone_id;

            if ($horZone && $zoneId != $horZone->id) {
                abort(403, 'You can only view users from your zone.');
            }

            $query->where(function($q) use ($zoneId) {
                $q->whereHas('zones', fn($sq) => $sq->where('id', $zoneId))
                  ->orWhere(fn($sq) => $sq->hasCityInZone($zoneId));
            });
        }

        $users = $query->orderBy('username')->paginate(25)->withQueryString();

        $zones = $horZone 
            ? collect([$horZone])
            : Zone::notCancelled()->orderBy('name')->get(['id', 'name']);

        return view('users.index', compact('users', 'zones'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create(Request $request)
    {
        $horZone = $this->getUserZone();

        // Roles
        if ($horZone) {
            $roles = Role::whereNotIn('name', ['admin', 'hor'])->get();
        } else {
            $roles = Role::all();
        }

        // Users Dropdown (Managers)
        $usersQuery = User::notCancelled()->select('id', 'username', 'manager_id');
        
        if ($horZone) {
            $zoneId = $horZone->id;
            $usersQuery->where(function($q) use ($zoneId) {
                $q->where('id', Auth::id())
                  ->orWhere('manager_id', Auth::id())
                  ->orWhere(function($sq) use ($zoneId) {
                      $sq->whereHas('zones', fn($q) => $q->where('id', $zoneId))
                        ->orWhere(fn($subq) => $subq->hasCityInZone($zoneId));
                  });
            });
        }
        $users = $usersQuery->orderBy('username')->get();

        // CRITICAL FIX: Include members who have CANCELLED users as "Available"
        $pwMembers = PwMember::select('pw_members.*')
            ->leftJoin('users', function($join) {
                $join->on('users.pw_member_id', '=', 'pw_members.id')
                     ->where('users.cancelled', 0); // Only join if the user is ACTIVE
            })
            ->whereNull('users.id') // Keep if no ACTIVE user exists (Cancelled is ok)
            ->where('pw_members.cancelled', 0)
            ->where('pw_members.is_active', 1)
            ->orderBy('pw_members.first_name')
            ->orderBy('pw_members.last_name')
            ->limit(500)
            ->get();

        // Pre-fill logic
        $pwMember = null;
        if ($request->filled('pw_member_id')) {
            $pwMember = PwMember::with(['voter.city.zone'])->findOrFail($request->input('pw_member_id'));

            // Access Verification
            $user = Auth::user();
            if (!$user->hasRole('admin')) {
                $hasAccess = false;
                if ($user->hasRole('hor')) {
                    $zoneIds = $user->zones()->pluck('zones.id');
                    if ($pwMember->voter && $zoneIds->contains($pwMember->voter->city->zone_id)) {
                        $hasAccess = true;
                    }
                } else {
                    $cityIds = $user->cities()->pluck('cities.id');
                    if ($pwMember->voter && $cityIds->contains($pwMember->voter->city_id)) {
                        $hasAccess = true;
                    }
                }

                if (!$hasAccess) {
                    abort(403, 'You do not have access to this PW member');
                }
            }
        }

        return view('users.create', compact('roles', 'users', 'pwMembers', 'horZone', 'pwMember'));
    }

    /**
     * Store a newly created user
     * FIX: Handles Reactivation of cancelled users
     */
    public function store(Request $request)
    {
        $horZone = $this->getUserZone();

        $request->validate([
            // FIX: Allow if existing record is cancelled
            'pw_member_id' => [
                'required', 
                'exists:pw_members,id', 
                Rule::unique('users')->where(fn ($query) => $query->where('cancelled', 0))
            ],
            // FIX: Allow reusing username if previous owner is cancelled
            'username' => [
                'required', 'string', 'max:255', 
                Rule::unique('users')->where(fn ($query) => $query->where('cancelled', 0))
            ],
            // FIX: Allow reusing email if previous owner is cancelled
            'email' => [
                'required', 'string', 'email', 'max:255', 
                Rule::unique('users')->where(fn ($query) => $query->where('cancelled', 0))
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
            'manager_id' => ['nullable', 'exists:users,id'],
        ]);

        if ($horZone) {
            if (in_array($request->role, ['admin', 'hor'])) {
                return response()->json(['success' => false, 'message' => 'You cannot create users with admin or HOR roles.'], 403);
            }

            if ($request->manager_id) {
                $manager = User::find($request->manager_id);
                if ($manager->id != Auth::id() && $manager->manager_id != Auth::id()) {
                    return response()->json(['success' => false, 'message' => 'You can only assign managers that report to you or yourself.'], 403);
                }
            }
        }

        return DB::transaction(function() use ($request) {
            $pwMember = PwMember::findOrFail($request->pw_member_id);
            $mobile = $this->formatMobile($pwMember->phone);

            // FIX: Check if a user (cancelled or not) already exists for this member
            // We use 'where' directly because global scope might not be applied, or we want to find cancelled ones explicitly.
            $existingUser = User::where('pw_member_id', $request->pw_member_id)->first();

            if ($existingUser) {
                // REACTIVATE and UPDATE existing user
                $existingUser->update([
                    'username' => $request->username,
                    'email' => $request->email,
                    'mobile' => $mobile,
                    'password' => Hash::make($request->password),
                    'manager_id' => $request->manager_id ?: null,
                    'cancelled' => 0 // Reactivate
                ]);
                $user = $existingUser;
            } else {
                // CREATE new user
                $user = User::create([
                    'pw_member_id' => $request->pw_member_id,
                    'username' => $request->username,
                    'email' => $request->email,
                    'mobile' => $mobile,
                    'password' => Hash::make($request->password),
                    'manager_id' => $request->manager_id ?: null,
                ]);
            }

            // Sync roles (replaces old roles if reactivating)
            $user->syncRoles([$request->role]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $user->id,
                'redirect' => route('users.assign-location', $user->id)
            ]);
        });
    }

    /**
     * Show location assignment form
     */
    public function showAssignLocation($id)
    {
        $user = User::with(['manager.zones', 'roles', 'zones'])->findOrFail($id);

        if (!$this->canManageUser($user)) {
            abort(403, 'You do not have permission to assign locations to this user.');
        }

        $horZone = $this->getUserZone();

        $managerZone = null;
        if ($user->manager_id && $user->manager_id != $user->id) {
            if ($user->manager && $user->manager->zones->isNotEmpty()) {
                $managerZone = $user->manager->zones->first();
            }
        }

        $canAssignZone = (!$user->manager_id || $user->manager_id == $user->id) && !$horZone;

        return view('users.assign-location', compact('user', 'managerZone', 'canAssignZone', 'horZone'));
    }

    /**
     * Get available locations based on type and manager zone
     */
    public function getAvailableLocations(Request $request, $userId)
    {
        $user = User::with('zones')->findOrFail($userId);
        $type = $request->type;
        $horZone = $this->getUserZone();

        if ($type === 'zone') {
            if ($horZone) return response()->json([]);

            if (!$user->manager_id || $user->manager_id == $user->id) {
                $zones = Zone::whereNull('user_id')
                    ->with('district.governorate:id,name')
                    ->orderBy('name')
                    ->get(['id', 'name', 'name_ar', 'district_id']);

                return response()->json($zones->map(function($zone) {
                    return [
                        'id' => $zone->id,
                        'name' => $zone->name,
                        'name_ar' => $zone->name_ar,
                        'location' => ($zone->district->governorate->name ?? '') . ', ' . ($zone->district->name ?? '')
                    ];
                }));
            }
            return response()->json([]);
        } 
        
        if ($type === 'city') {
            $query = City::with('zone.district.governorate:id,name')
                         ->select(['id', 'name', 'name_ar', 'zone_id']);

            if ($horZone) {
                $query->where('zone_id', $horZone->id);
            } else {
                if ($user->manager_id && $user->manager_id != $user->id) {
                    $manager = User::with('zones')->find($user->manager_id);
                    if ($manager && $manager->zones->isNotEmpty()) {
                        $query->where('zone_id', $manager->zones->first()->id);
                    }
                } else if ($user->zones->isNotEmpty()) {
                    $query->where('zone_id', $user->zones->first()->id);
                }
            }

            $locations = $query->orderBy('name')->get()->map(function($city) {
                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'name_ar' => $city->name_ar,
                    'location' => ($city->zone->district->name ?? '') . ', ' . ($city->zone->name ?? '')
                ];
            });

            return response()->json($locations);
        }

        return response()->json([]);
    }

    /**
     * Assign location to user
     */
    public function assignLocation(Request $request, $id)
    {
        $request->validate([
            'location_type' => ['required', 'in:zone,city,none'],
            'location_id' => ['nullable', 'integer', 'required_if:location_type,zone,city'],
        ]);

        $user = User::findOrFail($id);
        $horZone = $this->getUserZone();

        if (!$this->canManageUser($user)) {
            return response()->json(['success' => false, 'message' => 'Permission denied'], 403);
        }

        if ($horZone) {
            if ($request->location_type == 'zone') {
                return response()->json(['success' => false, 'message' => 'You cannot assign zones.'], 403);
            }
            if ($request->location_type == 'city') {
                $city = City::find($request->location_id);
                if (!$city || $city->zone_id != $horZone->id) {
                    return response()->json(['success' => false, 'message' => 'City not in your zone.'], 403);
                }
            }
        }

        return DB::transaction(function() use ($request, $user) {
            switch ($request->location_type) {
                case 'zone':
                    Zone::where('id', $request->location_id)->update(['user_id' => $user->id]);
                    break;
                case 'city':
                    $city = City::findOrFail($request->location_id);
                    $city->assignUser($user->id);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Location assigned successfully',
                'redirect' => route('users.index')
            ]);
        });
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        // Use setRelation for cities because it's not a standard relation
        $user = User::with(['roles', 'zones', 'manager.zones'])->findOrFail($id);
        $user->setRelation('cities', $user->cities());

        if (!$this->canManageUser($user)) {
            abort(403, 'You do not have permission to edit this user.');
        }

        $horZone = $this->getUserZone();

        if ($horZone) {
            $roles = Role::whereNotIn('name', ['admin', 'hor'])->get();
        } else {
            $roles = Role::all();
        }

        $currentLocation = null;
        $locationType = null;

        if ($user->zones->isNotEmpty()) {
            $currentLocation = $user->zones->first();
            $locationType = 'zone';
        } elseif ($user->cities->isNotEmpty()) {
            $currentLocation = $user->cities->first();
            $locationType = 'city';
        }

        $managerZone = null;
        if ($user->manager_id && $user->manager_id != $user->id) {
            if ($user->manager && $user->manager->zones->isNotEmpty()) {
                $managerZone = $user->manager->zones->first();
            }
        }

        return view('users.edit', compact('user', 'roles', 'currentLocation', 'locationType', 'managerZone', 'horZone'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $horZone = $this->getUserZone();

        if (!$this->canManageUser($user)) {
            return response()->json(['success' => false, 'message' => 'Permission denied'], 403);
        }

        $request->validate([
            'role' => ['required', 'exists:roles,name'],
            'location_type' => ['nullable', 'in:zone,city,none'],
            'location_id' => ['nullable', 'integer', 'required_if:location_type,zone,city'],
        ]);

        if ($horZone) {
            if (in_array($request->role, ['admin', 'hor'])) {
                return response()->json(['success' => false, 'message' => 'Invalid role'], 403);
            }
            if ($request->location_type == 'zone') {
                return response()->json(['success' => false, 'message' => 'Cannot assign zones'], 403);
            }
            if ($request->location_type == 'city') {
                $city = City::find($request->location_id);
                if (!$city || $city->zone_id != $horZone->id) {
                    return response()->json(['success' => false, 'message' => 'Invalid city'], 403);
                }
            }
        }

        return DB::transaction(function() use ($request, $user) {
            $user->syncRoles([$request->role]);

            // Clear old locations
            Zone::where('user_id', $user->id)->update(['user_id' => null]);
            
            City::notCancelled()
                ->whereJsonContains('user_id', (string)$user->id)
                ->get()
                ->each(function ($city) use ($user) {
                    $city->removeUser($user->id);
                });

            // Assign new location
            if ($request->location_type != 'none') {
                if ($request->location_type == 'zone') {
                    Zone::where('id', $request->location_id)->update(['user_id' => $user->id]);
                } elseif ($request->location_type == 'city') {
                    City::findOrFail($request->location_id)->assignUser($user->id);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'redirect' => route('users.index')
            ]);
        });
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (!$this->canManageUser($user)) {
            return response()->json(['success' => false, 'message' => 'Permission denied'], 403);
        }

        if ($user->id == Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Cannot delete yourself'], 403);
        }

        return DB::transaction(function() use ($user) {
            Zone::where('user_id', $user->id)->update(['user_id' => null]);

            City::notCancelled()
                ->whereJsonContains('user_id', (string)$user->id)
                ->get()
                ->each(function ($city) use ($user) {
                    $city->removeUser($user->id);
                });

            User::notCancelled()->where('manager_id', $user->id)->update(['manager_id' => null]);

            $user->setAttribute('cancelled', 1);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        });
    }

    /**
     * Get authenticated user's PW member info
     */
    public function getPwMemberInfo()
    {
        $user = Auth::user();
        $pwMember = $user->pwMember;

        if (!$pwMember) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not linked to a PW member profile'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'info' => [
                'full_name' => trim("{$pwMember->first_name} {$pwMember->father_name} {$pwMember->last_name}"),
                'phone' => $pwMember->phone ?? 'N/A'
            ]
        ]);
    }

    /**
     * Format mobile number to 961XXXXXXXX
     */
    private function formatMobile($mobile)
    {
        $mobile = preg_replace('/[\s\-\+]/', '', $mobile);

        if (substr($mobile, 0, 1) == '0') {
            $mobile = substr($mobile, 1);
        }

        if (substr($mobile, 0, 3) != '961') {
            $mobile = '961' . $mobile;
        }

        return $mobile;
    }
}