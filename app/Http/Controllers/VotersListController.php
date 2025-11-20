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

        // Build the query
        $query = Voter::with(['city.zone', 'pwMember'])
            ->where('cancelled', 0);

        // Apply access control based on user role
        if (!$user->hasRole('admin')) {
            if ($user->hasRole('hor')) {
                // HOR can see voters from their zones
                $zoneIds = $user->zones()->pluck('zones.id');
                $query->whereHas('city', function($q) use ($zoneIds) {
                    $q->whereIn('zone_id', $zoneIds);
                });
            } else {
                // Other users can see voters from their cities
                $cityIds = $user->cities()->pluck('cities.id');
                $query->whereIn('city_id', $cityIds);
            }
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('mother_full_name', 'like', "%{$search}%")
                  ->orWhere('register_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Zone filter (admin only)
        if ($user->hasRole('admin') && $request->filled('zone_id')) {
            $query->whereHas('city', function($q) use ($request) {
                $q->where('zone_id', $request->input('zone_id'));
            });
        }

        // City filter (for HOR and admins)
        if (($user->hasRole('admin') || $user->hasRole('hor')) && $request->filled('city_id')) {
            $query->where('city_id', $request->input('city_id'));
        }

        $voters = $query->orderBy('first_name')
                        ->orderBy('father_name')
                        ->orderBy('last_name')
                        ->paginate(50)
                        ->appends($request->all());

        // Get filters data
        $zones = $user->hasRole('admin') ? Zone::where('cancelled', 0)->orderBy('name')->get() : collect();
        $cities = collect();

        if ($user->hasRole('admin')) {
            $cities = City::where('cancelled', 0)->orderBy('name')->get();
        } elseif ($user->hasRole('hor')) {
            $zoneIds = $user->zones()->pluck('zones.id');
            $cities = City::where('cancelled', 0)->whereIn('zone_id', $zoneIds)->orderBy('name')->get();
        }

        return view('voters-list.index', compact('voters', 'zones', 'cities'));
    }

    /**
     * AJAX search for voters
     */
    public function search(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search', '');

        // Enforce minimum 2 characters for performance
        if (!$search || strlen($search) < 2) {
            return response()->json([]);
        }

        $query = Voter::with(['city.zone'])
            ->where('cancelled', 0);

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
                  ->orWhere('mother_full_name', 'like', "%{$search}%")
                  ->orWhere('register_number', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

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
