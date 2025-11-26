<?php

namespace App\Http\Controllers;

use App\Models\PwMember;
use App\Models\Voter;
use App\Models\User;
use App\Models\Zone;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PwMemberController extends Controller
{
    /**
     * Display a listing of PW members
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Optimize Filter Loading (Lazy load unless needed)
        $zones = $user->hasRole('admin') ? Zone::where('cancelled', 0)->orderBy('name')->get() : collect();
        $cities = collect();

        if ($user->hasRole('admin')) {
            $cities = City::where('cancelled', 0)->orderBy('name')->get(['id', 'name', 'zone_id']);
        } elseif ($user->hasRole('hor')) {
            $zoneIds = $user->zones()->pluck('zones.id');
            $cities = City::where('cancelled', 0)->whereIn('zone_id', $zoneIds)->orderBy('name')->get(['id', 'name']);
        }

        $search = $request->input('search');
        $search = $search && strlen(trim($search)) >= 2 ? trim($search) : null;

        // Store search query in session for later retrieval
        if ($search) {
            $request->session()->put('pw_members_search', $request->all());
        }

        if (!$search) {
            return view('pw-members.index', [
                'members' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1),
                'zones' => $zones,
                'cities' => $cities
            ]);
        }

        $query = PwMember::with(['voter.city.zone', 'user'])
            ->where('cancelled', 0);

        // --- OPTIMIZATION: Access Control via ID Lists (Avoids whereHas subqueries) ---
        $allowedCityIds = [];

        if (!$user->hasRole('admin')) {
            if ($user->hasRole('hor')) {
                $zoneIds = $user->zones()->pluck('zones.id');
                $allowedCityIds = City::whereIn('zone_id', $zoneIds)->pluck('id')->toArray();
            } else {
                $allowedCityIds = $user->cities()->pluck('cities.id')->toArray();
            }

            // Filter members based on their linked Voter's location
            // This requires a join because city_id is on the voters table, not pw_members
            $query->whereHas('voter', function ($q) use ($allowedCityIds) {
                $q->whereIn('city_id', $allowedCityIds);
            });
        }

        // Specific Filters
        if ($request->filled('status')) {
            $isActive = $request->input('status') == 'active';
            $query->where('is_active', $isActive);
        }

        // --- OPTIMIZATION: Smart Search ---
        $isNumeric = is_numeric($search);

        if ($isNumeric) {
            // Numeric Search: Phone
            $query->where('phone', 'like', $search . '%');
        } else {
            // Text Search: FullText for Name OR Standard Like for Email
            $query->where(function ($q) use ($search) {
                // High performance Name Match
                $q->whereRaw("MATCH(first_name, father_name, last_name) AGAINST(? IN BOOLEAN MODE)", [$search . '*'])
                    // Email usually requires standard LIKE
                    ->orWhere('email', 'like', $search . '%');
            });
        }

        // Ordering
        if ($isNumeric) {
            $query->orderBy('first_name');
        }

        $members = $query->paginate(min((int) $request->input('per_page', 25), 100))
            ->appends($request->all());

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
            'first_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mother_full_name' => 'required|string|max:255',
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
    public function show($id, Request $request)
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

        // Get search parameters from session
        $searchParams = $request->session()->get('pw_members_search', []);

        return view('pw-members.show', compact('member', 'searchParams'));
    }

    /**
     * Show the form for editing the specified PW member
     */
    public function edit($id, Request $request)
    {
        $user = Auth::user();

        // Check permission
        if (!$user->hasRole('admin') && !$user->hasRole('hor') && !$user->hasRole('fc')) {
            abort(403);
        }

        $member = PwMember::with(['voter.city.zone'])->findOrFail($id);

        // Check access
        if (!$user->hasRole('admin') && !$user->hasRole('fc')) {
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

        // Get search parameters from session
        $searchParams = $request->session()->get('pw_members_search', []);

        return view('pw-members.edit', compact('member', 'searchParams'));
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
            'first_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'mother_full_name' => 'required|string|max:255',
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
        $search = trim($request->input('search', ''));

        if (strlen($search) < 2) return response()->json([]);

        $query = PwMember::with(['voter.city.zone'])->where('cancelled', 0);

        // Simple Access Control
        if (!$user->hasRole('admin')) {
            // (Same logic as index: simplified for brevity, ideally extract to Trait)
            $allowedCityIds = $user->hasRole('hor')
                ? City::whereIn('zone_id', $user->zones()->pluck('zones.id'))->pluck('id')
                : $user->cities()->pluck('cities.id');

            $query->whereHas('voter', fn($q) => $q->whereIn('city_id', $allowedCityIds));
        }

        // Smart Search
        if (is_numeric($search)) {
            $query->where('phone', 'like', $search . '%');
        } else {
            $query->whereRaw("MATCH(first_name, father_name, last_name) AGAINST(? IN BOOLEAN MODE)", [$search . '*']);
        }

        return response()->json($query->limit(20)->get());
    }
    /**
     * AJAX search for voters without PW members
     */
    /**
     * AJAX search for voters without PW members
     * CRITICAL OPTIMIZATION AREA
     */
    public function searchAvailableVoters(Request $request)
    {
        try {
            $user = Auth::user();
            $search = trim($request->input('search', ''));
            $excludeVoterId = $request->input('exclude_voter_id');

            if (strlen($search) < 2) return response()->json([]);

            // 1. Start with Voter Query
            $query = Voter::query()
                ->select('voters_list.*') // Important for JOINs
                ->with(['city.zone'])
                ->where('voters_list.cancelled', 0);

            // 2. OPTIMIZATION: Use LEFT JOIN instead of whereDoesntHave
            // "Find voters where the pw_members ID is NULL"
            // This is significantly faster on large datasets than a subquery
            $query->leftJoin('pw_members', function ($join) {
                $join->on('voters_list.id', '=', 'pw_members.voter_id')
                    ->where('pw_members.cancelled', 0); // Only care about active members
            });

            // 3. Filter Logic
            if ($excludeVoterId) {
                // If editing, allow NULL (no member) OR the current voter ID
                $query->where(function ($q) use ($excludeVoterId) {
                    $q->whereNull('pw_members.id')
                        ->orWhere('voters_list.id', $excludeVoterId);
                });
            } else {
                // If creating, MUST be NULL
                $query->whereNull('pw_members.id');
            }

            // 4. Permission Logic (Use WhereIn)
            if (!$user->hasRole('admin')) {
                $allowedCityIds = $user->hasRole('hor')
                    ? City::whereIn('zone_id', $user->zones()->pluck('zones.id'))->pluck('id')
                    : $user->cities()->pluck('cities.id');

                $query->whereIn('voters_list.city_id', $allowedCityIds);
            }

            // 5. Smart Search (Reuse logic from VoterController)
            if (is_numeric($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('voters_list.register_number', $search)
                        ->orWhere('voters_list.phone', 'like', $search . '%');
                });
            } else {
                $query->whereRaw("MATCH(voters_list.first_name, voters_list.father_name, voters_list.last_name) AGAINST(? IN BOOLEAN MODE)", [$search . '*']);
            }

            // 6. Execute
            $voters = $query->limit(20)->get()->map(function ($voter) {
                return [
                    'id' => $voter->id,
                    'full_name' => "{$voter->first_name} {$voter->father_name} {$voter->last_name}",
                    'mother_full_name' => $voter->mother_full_name,
                    'register_number' => $voter->register_number,
                    'city' => $voter->city ? ['id' => $voter->city->id, 'name' => $voter->city->name] : null,
                ];
            });

            return response()->json($voters);
        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
            return response()->json(['error' => 'Search failed'], 500);
        }
    }
}
