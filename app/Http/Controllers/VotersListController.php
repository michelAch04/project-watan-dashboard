<?php

namespace App\Http\Controllers;

use App\Models\Voter;
use App\Models\PwMember;
use App\Models\Zone;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VotersListController extends Controller
{
    /**
     * Display a listing of voters (read-only)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // --- 1. OPTIMIZATION: Prepare Filters Efficiently ---
        // Don't load collections if not needed for the view immediately
        $zones = $user->hasRole('admin')
            ? Zone::where('cancelled', 0)->orderBy('name')->get()
            : collect();

        // For Cities, we only fetch what's needed for the dropdown
        $cities = collect();
        if ($user->hasRole('admin')) {
            $cities = City::where('cancelled', 0)->orderBy('name')->get(['id', 'name', 'zone_id']);
        } elseif ($user->hasRole('hor')) {
            $zoneIds = $user->zones()->pluck('zones.id');
            $cities = City::where('cancelled', 0)->whereIn('zone_id', $zoneIds)->orderBy('name')->get(['id', 'name']);
        }

        $search = $request->input('search');
        // Trim and ensure 2 chars
        $search = $search && strlen(trim($search)) >= 2 ? trim($search) : null;

        if (!$search) {
            // Return empty or default view
            // Using LengthAwarePaginator manually is fine, but returning empty view is cleaner if that's the logic
            return view('voters-list.index', [
                'voters' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1),
                'zones' => $zones,
                'cities' => $cities
            ]);
        }

        // --- 2. OPTIMIZATION: Build Scoped Query ---
        $query = Voter::with(['city.zone', 'pwMember']) // Eager load is good
            ->where('cancelled', 0);

        // --- 3. OPTIMIZATION: Replace whereHas with whereIn ---
        // Fetching IDs in PHP and passing them to SQL is faster than a subquery on large data
        $allowedCityIds = [];

        if ($user->hasRole('admin')) {
            if ($request->filled('zone_id')) {
                // Get cities for this zone
                $allowedCityIds = City::where('zone_id', $request->input('zone_id'))->pluck('id')->toArray();
                $query->whereIn('city_id', $allowedCityIds);
            }
            // Note: If admin selects specific city below, it overrides/intersects this
        } elseif ($user->hasRole('hor')) {
            $zoneIds = $user->zones()->pluck('zones.id');
            $allowedCityIds = City::whereIn('zone_id', $zoneIds)->pluck('id')->toArray();
            $query->whereIn('city_id', $allowedCityIds);
        } else {
            // Normal user
            $allowedCityIds = $user->cities()->pluck('cities.id')->toArray();
            $query->whereIn('city_id', $allowedCityIds);
        }

        // Specific City Filter (Admin/HOR)
        if ($request->filled('city_id')) {
            // Ensure the selected city is actually within their allowed list (security check)
            if (empty($allowedCityIds) || in_array($request->input('city_id'), $allowedCityIds)) {
                $query->where('city_id', $request->input('city_id'));
            }
        }

        // --- 4. OPTIMIZATION: Smart Search Logic ---
        // Don't search text fields if the user typed a number, and vice versa.

        $isNumeric = is_numeric($search);

        if ($isNumeric) {
            // SEARCH STRATEGY A: Numeric (Phone or Register Number)
            // Uses standard B-Tree indexes
            $query->where(function ($q) use ($search) {
                $q->where('register_number', $search) // Exact match is fastest
                    ->orWhere('phone', 'like', $search . '%'); // "Starts with" uses index
            });
        } else {
            // SEARCH STRATEGY B: Text (Names)
            // Uses the FULLTEXT index we created
            // Syntax: MATCH(cols) AGAINST(term IN BOOLEAN MODE)
            // We append '*' to allow partial matches (e.g., "Bass" finds "Bassam")
            $query->whereRaw(
                "MATCH(first_name, father_name, last_name) AGAINST(? IN BOOLEAN MODE)",
                [$search . '*']
            );

            // Note: We deliberately exclude Mother Name from the generic name search 
            // unless necessary, or you must add it to the FullText index in migration.
            // If you need mother name:
            // $query->orWhere('mother_full_name', 'like', $search . '%');
        }

        // --- 5. OPTIMIZATION: Pagination ---
        $perPage = min((int) $request->input('per_page', 25), 100);

        // Sorting:
        // If searching by text, Relevance is usually best, so we don't OrderBy.
        // If numeric search or no search, we sort.
        if ($isNumeric) {
            $query->orderBy('first_name');
        }

        // simplePaginate is faster than paginate because it doesn't count total rows
        // If you absolutely need page numbers (1, 2, 3... 500), use paginate().
        // If "Next/Prev" is enough, use simplePaginate().
        $voters = $query->paginate($perPage)->appends($request->all());

        return view('voters-list.index', compact('voters', 'zones', 'cities'));
    }

    public function search(Request $request)
    {
        $user = Auth::user();
        $search = trim($request->input('search', ''));

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $query = Voter::with(['city.zone'])->where('cancelled', 0);

        // Reuse the logic from index() for permissions (extracted to a helper usually)
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('hor')) {
                $zoneIds = $user->zones()->pluck('zones.id');
                $cityIds = City::whereIn('zone_id', $zoneIds)->pluck('id');
                $query->whereIn('city_id', $cityIds);
            } else {
                $cityIds = $user->cities()->pluck('cities.id');
                $query->whereIn('city_id', $cityIds);
            }
        }

        // Optimization: Smart Search
        if (is_numeric($search)) {
            $query->where('register_number', $search)
                ->orWhere('phone', 'like', $search . '%');
        } else {
            // Full Text Search
            $query->whereRaw(
                "MATCH(first_name, father_name, last_name) AGAINST(? IN BOOLEAN MODE)",
                [$search . '*']
            );
        }

        // Limit is crucial for AJAX
        $voters = $query->limit(20)->get();

        return response()->json($voters);
    }
    /**
     * Check if voter has PW member (AJAX)
     */
    public function checkPwMember($id)
    {
        $voter = Voter::findOrFail($id);
        $pwMember = PwMember::where('voter_id', $id)->where('cancelled', 0)->first();

        return response()->json([
            'has_pw_member' => !is_null($pwMember),
            'pw_member' => $pwMember
        ]);
    }
}
