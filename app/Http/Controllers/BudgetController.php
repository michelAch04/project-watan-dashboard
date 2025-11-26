<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\DiaperBudget;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BudgetController extends Controller
{
    /**
     * Display budgets for HOR's zone or all budgets for admin/FC
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Admin and FC can see all budgets, HOR can only see budgets for their own zones
        if ($user->hasRole('admin') || $user->hasRole('fc')) {
            $budgetsQuery = Budget::notCancelled()->with(['zone', 'transactions' => function($q) {
                $q->notCancelled()->orderBy('created_at', 'desc');
            }]);
            $diaperBudgetsQuery = DiaperBudget::notCancelled()->with(['zone', 'transactions' => function($q) {
                $q->notCancelled()->orderBy('created_at', 'desc');
            }]);
        } else {
            $budgetsQuery = Budget::notCancelled()->with(['zone', 'transactions' => function($q) {
                $q->notCancelled()->orderBy('created_at', 'desc');
            }])->whereHas('zone', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
            $diaperBudgetsQuery = DiaperBudget::notCancelled()->with(['zone', 'transactions' => function($q) {
                $q->notCancelled()->orderBy('created_at', 'desc');
            }])->whereHas('zone', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Filter by month if provided
        $month = $request->input('month');
        $year = $request->input('year');

        $budgets = $budgetsQuery->get()->map(function($budget) use ($month, $year) {
            // Check and refill budget if needed
            $budget->checkAndRefill();
            $budget->budget_type = 'regular';

            // Get transactions for selected month or all
            $transactions = $budget->transactions()
                ->when($month && $year, function($q) use ($month, $year) {
                    return $q->forMonth($year, $month);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $budget->filtered_transactions = $transactions;
            return $budget;
        });

        $diaperBudgets = $diaperBudgetsQuery->get()->map(function($budget) use ($month, $year) {
            // Check and refill budget if needed
            $budget->checkAndRefill();
            $budget->budget_type = 'diaper';

            // Get transactions for selected month or all
            $transactions = $budget->transactions()
                ->when($month && $year, function($q) use ($month, $year) {
                    return $q->forMonth($year, $month);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $budget->filtered_transactions = $transactions;
            return $budget;
        });

        // Merge both budget types
        $allBudgets = $budgets->concat($diaperBudgets);

        // Get available months for filter (last 12 months)
        $availableMonths = [];
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $availableMonths[] = [
                'month' => $date->month,
                'year' => $date->year,
                'label' => $date->format('F Y')
            ];
        }

        return view('budgets.index', compact('allBudgets', 'availableMonths', 'month', 'year'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $user = Auth::user();

        // FC can create budgets for all zones, HOR can only create for their own zone
        if ($user->hasRole('fc')) {
            $zones = Zone::all();
        } else {
            $zones = $user->zones;
        }

        if ($zones->isEmpty()) {
            abort(403, 'You must be assigned to a zone to create budgets.');
        }

        return view('budgets.create', compact('zones'));
    }

    /**
     * Store new budget (HOR and FC)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Only HOR and FC can create budgets
        if (!$user->hasRole('hor') && !$user->hasRole('fc')) {
            return response()->json([
                'success' => false,
                'message' => 'Only HOR and FC can create budgets'
            ], 403);
        }

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'monthly_amount_in_usd' => 'required|integer|min:1',
            'auto_refill_day' => 'required|integer|min:1|max:28',
            'zone_id' => 'required|exists:zones,id',
            'request_type' => 'required|string|in:humanitarian,public,diapers'
        ]);

        // Verify user owns this zone (skip for FC as they can manage all zones)
        if (!$user->hasRole('fc')) {
            $zone = Zone::findOrFail($validated['zone_id']);
            if ($zone->user_id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create budgets for your own zones'
                ], 403);
            }
        }

        // Set initial balance to monthly amount
        $validated['current_balance'] = $validated['monthly_amount_in_usd'];
        $validated['last_refill_date'] = now();

        $budget = Budget::create($validated);

        // Create initial refill transaction
        \App\Models\BudgetTransaction::create([
            'budget_id' => $budget->id,
            'type' => 'refill',
            'amount' => $budget->monthly_amount_in_usd,
            'balance_after' => $budget->current_balance,
            'description' => 'Initial budget creation'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Budget created successfully',
            'redirect' => route('budgets.index')
        ]);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $user = Auth::user();
        $budget = Budget::with('zone')->findOrFail($id);

        // Verify user owns this zone (skip for FC as they can manage all zones)
        if (!$user->hasRole('fc') && $budget->zone->user_id != $user->id) {
            abort(403, 'You can only edit budgets for your own zones');
        }

        // FC can see all zones, HOR can only see their own zones
        if ($user->hasRole('fc')) {
            $zones = Zone::all();
        } else {
            $zones = $user->zones;
        }

        return view('budgets.edit', compact('budget', 'zones'));
    }

    /**
     * Update budget (only description and monthly_amount_in_usd)
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $budget = Budget::with('zone')->findOrFail($id);

        // Verify user owns this zone (HOR and FC, admin can't edit)
        if (!$user->hasRole('hor') && !$user->hasRole('fc')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // HOR can only edit their own zone's budgets, FC can edit all
        if (!$user->hasRole('fc') && $budget->zone->user_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit budgets for your own zones'
            ], 403);
        }

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'monthly_amount_in_usd' => 'required|integer|min:1',
            'auto_refill_day' => 'required|integer|min:1|max:28',
            'request_type' => 'required|string|in:humanitarian,public,diapers'
        ]);

        $oldMonthlyAmount = $budget->monthly_amount_in_usd;

        $budget->update($validated);

        // If monthly amount changed, record an adjustment transaction
        if ($oldMonthlyAmount != $validated['monthly_amount_in_usd']) {
            $difference = $validated['monthly_amount_in_usd'] - $oldMonthlyAmount;
            \App\Models\BudgetTransaction::create([
                'budget_id' => $budget->id,
                'type' => 'adjustment',
                'amount' => 0, // No immediate balance change
                'balance_after' => $budget->current_balance,
                'description' => "Monthly amount changed from $$oldMonthlyAmount to $" . $validated['monthly_amount_in_usd']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Budget updated successfully',
            'redirect' => route('budgets.index')
        ]);
    }

    /**
     * Delete budget
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $budget = Budget::with('zone')->findOrFail($id);

        // Verify user owns this zone (skip for FC as they can manage all zones)
        if (!$user->hasRole('fc') && $budget->zone->user_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete budgets for your own zones'
            ], 403);
        }

        // Check if budget is being used by any active requests
        $hasHumanitarianRequests = $budget->humanitarianRequests()
            ->whereHas('requestHeader', function($q) {
                $q->notCancelled();
            })->count() > 0;

        $hasPublicRequests = $budget->publicRequests()
            ->whereHas('requestHeader', function($q) {
                $q->notCancelled();
            })->count() > 0;

        $hasDiapersRequests = $budget->diapersRequests()
            ->whereHas('requestHeader', function($q) {
                $q->notCancelled();
            })->count() > 0;

        if ($hasHumanitarianRequests || $hasPublicRequests || $hasDiapersRequests) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete budget that is being used by requests'
            ], 400);
        }

        // Soft delete by setting cancelled flag
        $budget->update(['cancelled' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Budget deleted successfully'
        ]);
    }

    /**
     * Get budgets for a specific zone (AJAX)
     * Used when HOR or FC is approving a request
     */
    public function getBudgetsForZone($zoneId, Request $request)
    {
        $user = Auth::user();
        $zone = Zone::findOrFail($zoneId);

        // Verify user owns this zone (skip for FC as they can access all zones)
        if (!$user->hasRole('fc') && $zone->user_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Get request type from query parameter to filter budgets
        $requestType = $request->query('request_type');

        $budgets = Budget::notCancelled()
            ->where('zone_id', $zoneId)
            ->when($requestType, function($q) use ($requestType) {
                return $q->forRequestType($requestType);
            })
            ->get();

        return response()->json([
            'success' => true,
            'budgets' => $budgets
        ]);
    }

    /**
     * Get budget preview for a request (AJAX)
     * Shows current budget and preview after deduction
     */
    public function getBudgetPreview(Request $request)
    {
        $validated = $request->validate([
            'budget_id' => 'required|exists:budgets,id',
            'amount' => 'required|numeric|min:0',
            'ready_date' => 'required|date'
        ]);

        $budget = Budget::findOrFail($validated['budget_id']);
        $readyDate = Carbon::parse($validated['ready_date']);
        $year = $readyDate->year;
        $month = $readyDate->month;

        $currentBudget = $budget->getRemainingBudgetForMonth($year, $month);
        $previewBudget = $currentBudget - $validated['amount'];
        $hasEnough = $previewBudget >= 0;

        return response()->json([
            'success' => true,
            'monthly_budget' => $budget->monthly_amount_in_usd,
            'current_remaining' => $currentBudget,
            'after_request' => $previewBudget,
            'has_enough' => $hasEnough
        ]);
    }

    /**
     * Get all budgets for user's zones (AJAX)
     * FC can see all zones, HOR can only see their own zones
     */
    public function getMyZoneBudgets()
    {
        $user = Auth::user();

        if ($user->hasRole('fc')) {
            $budgets = Budget::notCancelled()->get();
        } else {
            $budgets = Budget::notCancelled()->whereHas('zone', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();
        }

        return response()->json([
            'success' => true,
            'budgets' => $budgets
        ]);
    }
}
