<?php

namespace App\Http\Controllers;

use App\Models\PwMember;
use App\Models\Voter;
use App\Models\User;
use App\Models\Zone;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PwMemberController extends Controller
{
    /**
     * Display a listing of PW members
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Build the query
        $query = PwMember::with(['voter.city.zone', 'user'])
            ->notCancelled();

        // Apply access control based on user role
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('hor')) {
                // HOR can see members from their zones
                $zoneIds = $user->zones()->pluck('zones.id');
                $query->whereHas('voter.city', function($q) use ($zoneIds) {
                    $q->whereIn('zone_id', $zoneIds);
                });
            } else {
                // Other users can see members from their cities
                $cityIds = $user->cities()->pluck('cities.id');
                $query->whereHas('voter.city', function($q) use ($cityIds) {
                    $q->whereIn('id', $cityIds);
                });
            }
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Zone filter (admin only)
        if ($user->hasRole('admin') && $request->filled('zone_id')) {
            $query->whereHas('voter.city', function($q) use ($request) {
                $q->where('zone_id', $request->input('zone_id'));
            });
        }

        // City filter (for HOR and admins)
        if (($user->hasRole('admin') || $user->hasRole('hor')) && $request->filled('city_id')) {
            $query->whereHas('voter.city', function($q) use ($request) {
                $q->where('id', $request->input('city_id'));
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $members = $query->orderBy('name')->paginate(25)->appends($request->all());

        // Get filters data
        $zones = $user->hasRole('admin') ? Zone::where('cancelled', 0)->orderBy('name_en')->get() : collect();
        $cities = collect();

        if ($user->hasRole('admin')) {
            $cities = City::where('cancelled', 0)->orderBy('name')->get();
        } elseif ($user->hasRole('hor')) {
            $zoneIds = $user->zones()->pluck('zones.id');
            $cities = City::where('cancelled', 0)->whereIn('zone_id', $zoneIds)->orderBy('name')->get();
        }

        return view('pw-members.index', compact('members', 'zones', 'cities'));
    }

    /**
     * Show the form for creating a new PW member
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Check permission
        if (!$user->hasRole('admin') && !$user->hasRole('hor')) {
            abort(403, 'Unauthorized');
        }

        $voter = null;
        $voterId = $request->input('voter_id');

        if ($voterId) {
            $voter = Voter::with('city.zone')->findOrFail($voterId);

            // Verify user has access to this voter's city
            if (!$user->hasRole('admin')) {
                if ($user->hasRole('hor')) {
                    $zoneIds = $user->zones()->pluck('zones.id');
                    if (!$zoneIds->contains($voter->city->zone_id)) {
                        abort(403, 'You do not have access to this voter');
                    }
                } else {
                    $cityIds = $user->cities()->pluck('cities.id');
                    if (!$cityIds->contains($voter->city_id)) {
                        abort(403, 'You do not have access to this voter');
                    }
                }
            }
        }

        return view('pw-members.create', compact('voter'));
    }

    /**
     * Store a newly created PW member
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Check permission
        if (!$user->hasRole('admin') && !$user->hasRole('hor')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'voter_id' => 'required|exists:voters_list,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean'
        ]);

        // Verify user has access to this voter's city
        $voter = Voter::with('city.zone')->findOrFail($validated['voter_id']);

        if (!$user->hasRole('admin')) {
            if ($user->hasRole('hor')) {
                $zoneIds = $user->zones()->pluck('zones.id');
                if (!$zoneIds->contains($voter->city->zone_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have access to this voter'
                    ], 403);
                }
            } else {
                $cityIds = $user->cities()->pluck('cities.id');
                if (!$cityIds->contains($voter->city_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You do not have access to this voter'
                    ], 403);
                }
            }
        }

        // Check if voter already has a PW member
        if (PwMember::where('voter_id', $validated['voter_id'])->where('cancelled', 0)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This voter already has a PW member linked'
            ], 400);
        }

        $member = PwMember::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'PW Member created successfully',
            'redirect' => route('pw-members.index')
        ]);
    }

    /**
     * Display the specified PW member
     */
    public function show($id)
    {
        $user = Auth::user();
        $member = PwMember::with(['voter.city.zone', 'user'])->findOrFail($id);

        // Check access
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('hor')) {
                $zoneIds = $user->zones()->pluck('zones.id');
                if ($member->voter && !$zoneIds->contains($member->voter->city->zone_id)) {
                    abort(403);
                }
            } else {
                $cityIds = $user->cities()->pluck('cities.id');
                if ($member->voter && !$cityIds->contains($member->voter->city_id)) {
                    abort(403);
                }
            }
        }

        return view('pw-members.show', compact('member'));
    }

    /**
     * Show the form for editing the specified PW member
     */
    public function edit($id)
    {
        $user = Auth::user();

        // Check permission
        if (!$user->hasRole('admin') && !$user->hasRole('hor')) {
            abort(403);
        }

        $member = PwMember::with(['voter.city.zone'])->findOrFail($id);

        // Check access
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('hor')) {
                $zoneIds = $user->zones()->pluck('zones.id');
                if ($member->voter && !$zoneIds->contains($member->voter->city->zone_id)) {
                    abort(403);
                }
            } else {
                $cityIds = $user->cities()->pluck('cities.id');
                if ($member->voter && !$cityIds->contains($member->voter->city_id)) {
                    abort(403);
                }
            }
        }

        return view('pw-members.edit', compact('member'));
    }

    /**
     * Update the specified PW member
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        // Check permission
        if (!$user->hasRole('admin') && !$user->hasRole('hor')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $member = PwMember::with(['voter.city.zone'])->findOrFail($id);

        // Check access
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('hor')) {
                $zoneIds = $user->zones()->pluck('zones.id');
                if ($member->voter && !$zoneIds->contains($member->voter->city->zone_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized'
                    ], 403);
                }
            } else {
                $cityIds = $user->cities()->pluck('cities.id');
                if ($member->voter && !$cityIds->contains($member->voter->city_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized'
                    ], 403);
                }
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'is_active' => 'boolean'
        ]);

        $member->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'PW Member updated successfully',
            'redirect' => route('pw-members.index')
        ]);
    }

    /**
     * Remove the specified PW member (soft delete)
     */
    public function destroy($id)
    {
        $user = Auth::user();

        // Check permission
        if (!$user->hasRole('admin') && !$user->hasRole('hor')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $member = PwMember::with(['voter.city.zone'])->findOrFail($id);

        // Check access
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('hor')) {
                $zoneIds = $user->zones()->pluck('zones.id');
                if ($member->voter && !$zoneIds->contains($member->voter->city->zone_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized'
                    ], 403);
                }
            } else {
                $cityIds = $user->cities()->pluck('cities.id');
                if ($member->voter && !$cityIds->contains($member->voter->city_id)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized'
                    ], 403);
                }
            }
        }

        // Soft delete
        $member->update(['cancelled' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'PW Member deleted successfully'
        ]);
    }

    /**
     * AJAX search for PW members
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search', '');

        $query = PwMember::with(['voter.city.zone'])
            ->where('cancelled', 0);

        // Apply access control
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('hor')) {
                $zoneIds = $user->zones()->pluck('zones.id');
                $query->whereHas('voter.city', function($q) use ($zoneIds) {
                    $q->whereIn('zone_id', $zoneIds);
                });
            } else {
                $cityIds = $user->cities()->pluck('cities.id');
                $query->whereHas('voter.city', function($q) use ($cityIds) {
                    $q->whereIn('id', $cityIds);
                });
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $members = $query->limit(20)->get();

        return response()->json($members);
    }

    /**
     * AJAX search for voters without PW members
     */
    public function searchAvailableVoters(Request $request)
    {
        try {
            $user = Auth::user();
            $search = $request->input('search', '');
            $excludeVoterId = $request->input('exclude_voter_id'); // For edit form

            $query = Voter::with(['city.zone'])
                ->where('cancelled', 0);

            // Only show voters without active PW members
            if ($excludeVoterId) {
                // In edit mode, allow the current voter
                $query->whereDoesntHave('pwMember', function($q) use ($excludeVoterId) {
                    $q->where('cancelled', 0)
                      ->where('voter_id', '!=', $excludeVoterId);
                });
            } else {
                // In create mode, exclude all voters with active PW members
                $query->whereDoesntHave('pwMember', function($q) {
                    $q->where('cancelled', 0);
                });
            }

        // Apply access control
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('hor')) {
                $zoneIds = $user->zones()->pluck('zones.id');
                $query->whereHas('city', function($q) use ($zoneIds) {
                    $q->whereIn('zone_id', $zoneIds);
                });
            } else {
                $cityIds = $user->cities()->pluck('cities.id');
                $query->whereIn('city_id', $cityIds);
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('ro_number', 'like', "%{$search}%");
            });
        }

            $voters = $query->limit(20)->get()->map(function($voter) {
                $fullName = $voter->full_name ?: trim("{$voter->first_name} {$voter->father_name} {$voter->last_name}");
                return [
                    'id' => $voter->id,
                    'first_name' => $voter->first_name,
                    'father_name' => $voter->father_name,
                    'last_name' => $voter->last_name,
                    'full_name' => $fullName,
                    'phone' => $voter->phone,
                    'ro_number' => $voter->ro_number,
                    'city' => $voter->city ? [
                        'id' => $voter->city->id,
                        'name' => $voter->city->name,
                    ] : null,
                ];
            });

            return response()->json($voters);
        } catch (\Exception $e) {
            \Log::error('Error searching available voters: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to search voters', 'message' => $e->getMessage()], 500);
        }
    }
}
