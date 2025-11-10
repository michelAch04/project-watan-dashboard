<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Zone;
use App\Models\City;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Show password verification form
     */
    public function showVerifyPassword()
    {
        return view('users.verify-password');
    }

    /**
     * Verify admin password
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        // Store verification in session for 15 minutes
        session(['admin_password_verified' => now()]);

        $intendedUrl = session('intended_url', route('users.index'));
        session()->forget('intended_url');

        return response()->json([
            'success' => true,
            'redirect' => $intendedUrl
        ]);
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'manager', 'zones']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // Zone filter
        if ($request->filled('zone_id')) {
            $zoneId = $request->zone_id;
            $query->where(function($q) use ($zoneId) {
                // Zone managers
                $q->where(function($sq) use ($zoneId) {
                    $sq->whereHas('zones', function($q) use ($zoneId) {
                        $q->where('id', $zoneId);
                    });
                })
                // City managers - JSON user_id contains user id
                ->orWhere(function($sq) use ($zoneId) {
                    $sq->whereHas('cities', function($q) use ($zoneId) {
                        $q->where('zone_id', $zoneId);
                    });
                })
                // Village managers - JSON user_id contains user id
                ->orWhere(function($sq) use ($zoneId) {
                    $sq->whereHas('villages', function($q) use ($zoneId) {
                        $q->whereHas('city.zone', function($q) use ($zoneId) {
                            $q->where('id', $zoneId);
                        });
                    });
                });
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();
        $zones = Zone::orderBy('name')->get();

        return view('users.index', compact('users', 'zones'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $roles = Role::all();
        $users = User::orderBy('name')->get();
        
        return view('users.create', compact('roles', 'users'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'mobile' => ['required', 'string', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'exists:roles,name'],
            'manager_id' => ['nullable', 'exists:users,id'],
        ]);

        // Format mobile number
        $mobile = $this->formatMobile($request->mobile);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $mobile,
            'password' => Hash::make($request->password),
            'manager_id' => $request->manager_id ?: null,
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user_id' => $user->id
        ]);
    }

    /**
     * Show location assignment form
     */
    public function showAssignLocation($id)
    {
        $user = User::with(['manager', 'roles'])->findOrFail($id);
        
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

        return view('users.assign-location', compact('user', 'managerZone', 'canAssignZone'));
    }

    /**
     * Get available locations based on type and manager zone
     */
    public function getAvailableLocations(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $type = $request->type; // 'zone', 'city', or 'village'

        $locations = [];

        if ($type === 'zone') {
            // Only if user reports to themselves
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
        } elseif ($type === 'village') {
            // Show all villages in the zone, since multiple users can be assigned to the same village
            $query = Village::with('city.zone.district.governorate');
            
            // Filter by manager's zone if user reports to someone else
            if ($user->manager_id && $user->manager_id != $user->id) {
                $manager = User::find($user->manager_id);
                if ($manager->zones()->count() > 0) {
                    $managerZone = $manager->zones()->first();
                    $query->whereHas('city', function($q) use ($managerZone) {
                        $q->where('zone_id', $managerZone->id);
                    });
                }
            } else if ($user->zones()->count() > 0) {
                // If user is a zone manager, show all villages in their zone
                $userZone = $user->zones()->first();
                $query->whereHas('city', function($q) use ($userZone) {
                    $q->where('zone_id', $userZone->id);
                });
            }
            
            $locations = $query->orderBy('name')
                ->get()
                ->map(function($village) {
                    return [
                        'id' => $village->id,
                        'name' => $village->name,
                        'name_ar' => $village->name_ar,
                        'location' => $village->city->zone->name . ', ' . $village->city->name
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
            'location_type' => ['required', 'in:zone,city,village,none'],
            'location_id' => ['nullable', 'integer', 'required_if:location_type,zone,city,village'],
        ]);

        $user = User::findOrFail($id);

        switch ($request->location_type) {
            case 'zone':
                Zone::where('id', $request->location_id)->update(['user_id' => $user->id]);
                break;
            case 'city':
                // Add user to city's JSON array
                $city = City::findOrFail($request->location_id);
                $city->assignUser($user->id);
                break;
            case 'village':
                // Add user to village's JSON array
                $village = Village::findOrFail($request->location_id);
                $village->assignUser($user->id);
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
        $roles = Role::all();
        
        // Determine current location
        $currentLocation = null;
        $locationType = null;
        
        if ($user->zones()->count() > 0) {
            $currentLocation = $user->zones()->first();
            $locationType = 'zone';
        } elseif ($user->cities()->count() > 0) {
            $currentLocation = $user->cities()->first();
            $locationType = 'city';
        } elseif ($user->villages()->count() > 0) {
            $currentLocation = $user->villages()->first();
            $locationType = 'village';
        }

        // Get manager's zone for filtering
        $managerZone = null;
        if ($user->manager_id && $user->manager_id != $user->id) {
            $manager = User::find($user->manager_id);
            if ($manager->zones()->count() > 0) {
                $managerZone = $manager->zones()->first();
            }
        }

        return view('users.edit', compact('user', 'roles', 'currentLocation', 'locationType', 'managerZone'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role' => ['required', 'exists:roles,name'],
            'location_type' => ['nullable', 'in:zone,city,village,none'],
            'location_id' => ['nullable', 'integer', 'required_if:location_type,zone,city,village'],
        ]);

        // Update role
        $user->syncRoles([$request->role]);

        // Update location
        // First, remove current location assignment
        if ($user->zones()->count() > 0) {
            Zone::where('user_id', $user->id)->update(['user_id' => null]);
        }
        
        // Remove user from all cities they're assigned to
        City::whereJsonContains('user_id', $user->id)->each(function ($city) use ($user) {
            $city->removeUser($user->id);
        });
        
        // Remove user from all villages they're assigned to
        Village::whereJsonContains('user_id', $user->id)->each(function ($village) use ($user) {
            $village->removeUser($user->id);
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
                case 'village':
                    $village = Village::findOrFail($request->location_id);
                    $village->assignUser($user->id);
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
        City::whereJsonContains('user_id', $user->id)->each(function ($city) use ($user) {
            $city->removeUser($user->id);
        });
        
        // Remove user from all villages
        Village::whereJsonContains('user_id', $user->id)->each(function ($village) use ($user) {
            $village->removeUser($user->id);
        });

        // Update users who report to this user
        User::where('manager_id', $user->id)->update(['manager_id' => null]);

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
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