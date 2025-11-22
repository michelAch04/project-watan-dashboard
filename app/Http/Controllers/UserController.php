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
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // ... [Previous helper methods like getUserZone, isAdmin, canManageUser remain unchanged] ...
    /**
     * Get the authenticated user's zone (for HORs)
     */
    private function getUserZone()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return null; // Admin can see all zones
        }

        if ($user->hasRole('hor')) {
            // Get HOR's zone
            $zone = $user->zones()->first();
            return $zone;
        }

        return null;
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
     */
    private function canManageUser($targetUser)
    {
        if ($this->isAdmin()) {
            return true; // Admins can manage all users
        }

        $horZone = $this->getUserZone();
        if (!$horZone) {
            return false; // HOR without zone cannot manage users
        }

        // Check if HOR is the user's manager directly
        if($targetUser->manager_id === Auth::id()) {
            return true;
        }

        // Check if target user belongs to HOR's zone
        // User belongs to zone if:
        // 1. They manage the zone
        // 2. They manage a city in the zone
        $userZone = $targetUser->zones()->first();
        if ($userZone && $userZone->id === $horZone->id) {
            return true;
        }

        $userCities = City::notCancelled()->whereJsonContains('user_id', $targetUser->id)->get();
        foreach ($userCities as $city) {
            if ($city->zone_id === $horZone->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::notCancelled()->with(['roles', 'manager', 'zones', 'pwMember']);

        // If HOR, restrict to their zone only
        $horZone = $this->getUserZone();
        if ($horZone) {
            $zoneId = $horZone->id;
            // HOR can only see users in their zone
            $query->where(function($q) use ($zoneId) {
                // Zone managers
                $q->whereHas('zones', function($q) use ($zoneId) {
                    $q->where('id', $zoneId);
                })
                // City managers - users in cities within this zone
                ->orWhere(function($sq) use ($zoneId) {
                    $sq->hasCityInZone($zoneId);
                });
            });
        }
        else if(!($this->isAdmin() || $horZone)) {
            // HOR's that aren't assigned to a zone cannot see any users
            $query->whereRaw('1 = 0');
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%") // Changed from name to username
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // Zone filter (admin only, or HOR viewing their own zone)
        if ($request->filled('zone_id')) {
            $zoneId = $request->zone_id;

            // If HOR, only allow filtering by their own zone
            if ($horZone && $zoneId != $horZone->id) {
                abort(403, 'You can only view users from your zone.');
            }

            $query->where(function($q) use ($zoneId) {
                // Zone managers
                $q->whereHas('zones', function($q) use ($zoneId) {
                    $q->where('id', $zoneId);
                })
                // City managers - users in cities within this zone
                ->orWhere(function($sq) use ($zoneId) {
                    $sq->hasCityInZone($zoneId);
                });
            });
        }

        $users = $query->orderBy('username')->paginate(15)->withQueryString(); // Changed order by name to username

        // For HOR, only show their zone; for admin, show all zones
        $zones = $horZone ? Zone::notCancelled()->where('id', $horZone->id)->get() : Zone::notCancelled()->orderBy('name')->get();

        return view('users.index', compact('users', 'zones'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create(Request $request)
    {
        $horZone = $this->getUserZone();

        // For HORs, limit available roles (exclude admin and hor)
        if ($horZone) {
            $roles = Role::whereNotIn('name', ['admin', 'hor'])->get();
        } else {
            $roles = Role::all();
        }

        // For HORs, limit available managers to themselves and users that report to them in their zone
        if ($horZone) {
            $zoneId = $horZone->id;
            $users = User::where('id', Auth::id())
                ->orWhere(function($q) use ($zoneId) {
                    $q->where('manager_id', Auth::id())
                      ->where(function($sq) use ($zoneId) {
                          // Users in HOR's zone
                          $sq->whereHas('zones', function($q) use ($zoneId) {
                              $q->where('id', $zoneId);
                          })
                          ->orWhere(function($subq) use ($zoneId) {
                              $subq->hasCityInZone($zoneId);
                          });
                      });
                })
                ->orderBy('username') // Changed from name to username
                ->get();
        } else {
            $users = User::notCancelled()->orderBy('username')->get(); // Changed from name to username
        }

        // Get PW members that don't have users yet
        $pwMembers = PwMember::active()
            ->whereDoesntHave('user')
            ->orderBy('first_name') // Ensure sorting works with new field logic
            ->get();

        // Check if pre-filling from a PW member
        $pwMember = null;
        if ($request->filled('pw_member_id')) {
            $pwMember = PwMember::with(['voter.city.zone'])->findOrFail($request->input('pw_member_id'));

            // Verify access to this PW member
            $user = Auth::user();
            if (!$user->hasRole('admin') && !$user->hasRole('hor')) {
                abort(403, 'Unauthorized');
            }

            if (!$user->hasRole('admin')) {
                if ($user->hasRole('hor')) {
                    $zoneIds = $user->zones()->pluck('zones.id');
                    if ($pwMember->voter && !$zoneIds->contains($pwMember->voter->city->zone_id)) {
                        abort(403, 'You do not have access to this PW member');
                    }
                } else {
                    $cityIds = $user->cities()->pluck('cities.id');
                    if ($pwMember->voter && !$cityIds->contains($pwMember->voter->city_id)) {
                        abort(403, 'You do not have access to this PW member');
                    }
                }
            }
        }

        return view('users.create', compact('roles', 'users', 'pwMembers', 'horZone', 'pwMember'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $horZone = $this->getUserZone();

        $request->validate([
            'pw_member_id' => ['required', 'exists:pw_members,id', 'unique:users,pw_member_id'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'], // Added username validation
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
            'manager_id' => ['nullable', 'exists:users,id'],
        ]);

        // Additional validation for HORs
        if ($horZone) {
            // HOR cannot create admin or hor users
            if (in_array($request->role, ['admin', 'hor'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot create users with admin or HOR roles.'
                ], 403);
            }

            // HOR can only assign managers that are themselves or report to them
            if ($request->manager_id) {
                $manager = User::find($request->manager_id);
                if ($manager->id !== Auth::id() && $manager->manager_id !== Auth::id()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only assign managers that report to you or yourself.'
                    ], 403);
                }
            }
        }

        // Get PW member details
        $pwMember = PwMember::findOrFail($request->pw_member_id);

        // Format mobile number
        $mobile = $this->formatMobile($pwMember->phone);

        $user = User::create([
            'pw_member_id' => $request->pw_member_id,
            'username' => $request->username, // Set username manually instead of using PW Member name
            'email' => $request->email,
            'mobile' => $mobile,
            'password' => Hash::make($request->password),
            'manager_id' => $request->manager_id ?: null,
        ]);

        $user->assignRole($request->role);

        // Automatically redirect to assign-location page
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user_id' => $user->id,
            'redirect' => route('users.assign-location', $user->id)
        ]);
    }

    // ... [Rest of the controller methods: showAssignLocation, getAvailableLocations, assignLocation, edit, update, destroy, formatMobile remain unchanged except for potentially ordering by username if needed in specific queries] ...
    // For brevity, I have not repeated the unchanged methods below this point unless you need them updated as well.
    // However, formatMobile is included in the original file so I will ensure the file ends correctly.

    /**
     * Show location assignment form
     */
    public function showAssignLocation($id)
    {
        $user = User::with(['manager', 'roles'])->findOrFail($id);

        // Check if current user (HOR or admin) can manage this user
        if (!$this->canManageUser($user)) {
            abort(403, 'You do not have permission to assign locations to this user.');
        }

        $horZone = $this->getUserZone();

        // Get manager's zone to filter available locations
        $managerZone = null;
        if ($user->manager_id && $user->manager_id != $user->id) {
            $manager = User::find($user->manager_id);
            if ($manager->zones()->count() > 0) {
                $managerZone = $manager->zones()->first();
            }
        }

        // User can only be assigned zone if they report to themselves
        $canAssignZone = !$user->manager_id || $user->manager_id == $user->id;

        // If HOR, they cannot assign zones (only cities in their zone)
        if ($horZone) {
            $canAssignZone = false;
        }

        return view('users.assign-location', compact('user', 'managerZone', 'canAssignZone', 'horZone'));
    }

    /**
     * Get available locations based on type and manager zone
     */
    public function getAvailableLocations(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $type = $request->type; // 'zone' or 'city'

        $horZone = $this->getUserZone();
        $locations = [];

        if ($type === 'zone') {
            // HORs cannot assign zones
            if ($horZone) {
                return response()->json([]);
            }

            // Only if user reports to themselves and admin is assigning
            if (!$user->manager_id || $user->manager_id == $user->id) {
                $locations = Zone::whereNull('user_id')
                    ->with('district.governorate')
                    ->orderBy('name')
                    ->get()
                    ->map(function($zone) {
                        return [
                            'id' => $zone->id,
                            'name' => $zone->name,
                            'name_ar' => $zone->name_ar,
                            'location' => $zone->district->governorate->name . ', ' . $zone->district->name
                        ];
                    });
            }
        } elseif ($type === 'city') {
            // Show all cities in the zone, since multiple users can be assigned to the same city
            $query = City::with('zone.district.governorate');

            // If HOR, restrict to their zone only
            if ($horZone) {
                $query->where('zone_id', $horZone->id);
            } else {
                // Filter by manager's zone if user reports to someone else
                if ($user->manager_id && $user->manager_id != $user->id) {
                    $manager = User::find($user->manager_id);
                    if ($manager->zones()->count() > 0) {
                        $managerZone = $manager->zones()->first();
                        $query->where('zone_id', $managerZone->id);
                    }
                } else if ($user->zones()->count() > 0) {
                    // If user is a zone manager, show all cities in their zone
                    $userZone = $user->zones()->first();
                    $query->where('zone_id', $userZone->id);
                }
            }

            $locations = $query->orderBy('name')
                ->get()
                ->map(function($city) {
                    return [
                        'id' => $city->id,
                        'name' => $city->name,
                        'name_ar' => $city->name_ar,
                        'location' => $city->zone->district->name . ', ' . $city->zone->name
                    ];
                });
        }

        return response()->json($locations);
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

        // Check if current user can manage this user
        if (!$this->canManageUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to assign locations to this user.'
            ], 403);
        }

        // HOR validation
        if ($horZone) {
            // HOR cannot assign zones
            if ($request->location_type === 'zone') {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot assign zones. Only cities in your zone.'
                ], 403);
            }

            // HOR can only assign cities from their zone
            if ($request->location_type === 'city') {
                $city = City::find($request->location_id);
                if (!$city || $city->zone_id !== $horZone->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only assign cities from your zone.'
                    ], 403);
                }
            }

        }

        switch ($request->location_type) {
            case 'zone':
                Zone::where('id', $request->location_id)->update(['user_id' => $user->id]);
                break;
            case 'city':
                // Add user to city's JSON array
                $city = City::findOrFail($request->location_id);
                $city->assignUser($user->id);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => 'Location assigned successfully',
            'redirect' => route('users.index')
        ]);
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        $user = User::with(['roles', 'zones', 'manager'])->findOrFail($id);

        // Check if current user can manage this user
        if (!$this->canManageUser($user)) {
            abort(403, 'You do not have permission to edit this user.');
        }

        $horZone = $this->getUserZone();

        // For HORs, limit available roles (exclude admin and hor)
        if ($horZone) {
            $roles = Role::whereNotIn('name', ['admin', 'hor'])->get();
        } else {
            $roles = Role::all();
        }

        // Determine current location
        $currentLocation = null;
        $locationType = null;

        if ($user->zones()->count() > 0) {
            $currentLocation = $user->zones()->first();
            $locationType = 'zone';
        } elseif ($user->cities()->count() > 0) {
            $currentLocation = $user->cities()->first();
            $locationType = 'city';
        }

        // Get manager's zone for filtering
        $managerZone = null;
        if ($user->manager_id && $user->manager_id != $user->id) {
            $manager = User::find($user->manager_id);
            if ($manager->zones()->count() > 0) {
                $managerZone = $manager->zones()->first();
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

        // Check if current user can manage this user
        if (!$this->canManageUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to edit this user.'
            ], 403);
        }

        $request->validate([
            'role' => ['required', 'exists:roles,name'],
            'location_type' => ['nullable', 'in:zone,city,none'],
            'location_id' => ['nullable', 'integer', 'required_if:location_type,zone,city'],
        ]);

        // HOR validation
        if ($horZone) {
            // HOR cannot assign admin or hor roles
            if (in_array($request->role, ['admin', 'hor'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot assign admin or HOR roles.'
                ], 403);
            }

            // HOR cannot assign zones
            if ($request->location_type === 'zone') {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot assign zones. Only cities in your zone.'
                ], 403);
            }

            // HOR can only assign cities from their zone
            if ($request->location_type === 'city') {
                $city = City::find($request->location_id);
                if (!$city || $city->zone_id !== $horZone->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only assign cities from your zone.'
                    ], 403);
                }
            }

        }

        // Update role
        $user->syncRoles([$request->role]);

        // Update location
        // First, remove current location assignment
        if ($user->zones()->count() > 0) {
            Zone::where('user_id', $user->id)->update(['user_id' => null]);
        }

        // Remove user from all cities they're assigned to
        City::notCancelled()->whereJsonContains('user_id', $user->id)->each(function ($city) use ($user) {
            $city->removeUser($user->id);
        });

        // Assign new location
        if ($request->location_type !== 'none') {
            switch ($request->location_type) {
                case 'zone':
                    Zone::where('id', $request->location_id)->update(['user_id' => $user->id]);
                    break;
                case 'city':
                    $city = City::findOrFail($request->location_id);
                    $city->assignUser($user->id);
                    break;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'redirect' => route('users.index')
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Check if current user can manage this user
        if (!$this->canManageUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this user.'
            ], 403);
        }

        // Prevent deleting yourself
        if ($user->id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account'
            ], 403);
        }

        // Remove location assignments
        Zone::where('user_id', $user->id)->update(['user_id' => null]);

        // Remove user from all cities
        City::notCancelled()->whereJsonContains('user_id', $user->id)->each(function ($city) use ($user) {
            $city->removeUser($user->id);
        });

        // Update users who report to this user (only non-cancelled users)
        User::notCancelled()->where('manager_id', $user->id)->update(['manager_id' => null]);

        $user->setAttribute('cancelled', 1);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get authenticated user's PW member info (for auto-filling public requests)
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

        if (substr($mobile, 0, 1) === '0') {
            $mobile = substr($mobile, 1);
        }

        if (substr($mobile, 0, 3) !== '961') {
            $mobile = '961' . $mobile;
        }

        return $mobile;
    }
}