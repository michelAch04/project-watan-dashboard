<?php

namespace App\Http\Controllers;

use App\Models\DiaperBudget;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DiaperBudgetController extends Controller
{
    /**
     * Display diaper budgets for HOR's zone or all diaper budgets for admin/FC
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Admin and FC can see all budgets, HOR can only see budgets for their own zones
        if ($user->hasRole('admin') || $user->hasRole('fc')) {
            $budgetsQuery = DiaperBudget::notCancelled()->with(['zone', 'transactions' => function($q) {
                $q->notCancelled()->orderBy('created_at', 'desc');
            }]);
        } else {
            $budgetsQuery = DiaperBudget::notCancelled()->with(['zone', 'transactions' => function($q) {
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

        return view('diaper-budgets.index', compact('budgets', 'availableMonths', 'month', 'year'));
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
            abort(403, 'You must be assigned to a zone to create diaper budgets.');
        }

        return view('diaper-budgets.create', compact('zones'));
    }

    /**
     * Store new diaper budget (HOR and FC)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Only HOR and FC can create budgets
        if (!$user->hasRole('hor') && !$user->hasRole('fc')) {
            return response()->json([
                'success' => false,
                'message' => 'Only HOR and FC can create diaper budgets'
            ], 403);
        }

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'monthly_restock' => 'required|array',
            'monthly_restock.*' => 'required|integer|min:0',
            'auto_refill_day' => 'required|integer|min:1|max:28',
            'zone_id' => 'required|exists:zones,id'
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

        // Set initial stock to monthly restock
        $validated['current_stock'] = $validated['monthly_restock'];
        $validated['last_refill_date'] = now();

        $budget = DiaperBudget::create($validated);

        // Create initial refill transaction
        \App\Models\DiaperBudgetTransaction::create([
            'diaper_budget_id' => $budget->id,
            'type' => 'refill',
            'quantity_change' => $budget->monthly_restock,
            'stock_after' => $budget->current_stock,
            'description' => 'Initial diaper budget creation'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Diaper budget created successfully',
            'redirect' => route('budgets.index')
        ]);
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $user = Auth::user();
        $budget = DiaperBudget::with('zone')->findOrFail($id);

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

        return view('diaper-budgets.edit', compact('budget', 'zones'));
    }

    /**
     * Update diaper budget (only description and monthly_restock)
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $budget = DiaperBudget::with('zone')->findOrFail($id);

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
            'monthly_restock' => 'required|array',
            'monthly_restock.*' => 'required|integer|min:0',
            'auto_refill_day' => 'required|integer|min:1|max:28'
        ]);

        $oldMonthlyRestock = $budget->monthly_restock;

        $budget->update($validated);

        // If monthly restock changed, record an adjustment transaction
        if ($oldMonthlyRestock != $validated['monthly_restock']) {
            \App\Models\DiaperBudgetTransaction::create([
                'diaper_budget_id' => $budget->id,
                'type' => 'adjustment',
                'quantity_change' => ['note' => 'Monthly restock updated'], // No immediate stock change
                'stock_after' => $budget->current_stock,
                'description' => "Monthly restock changed from " . json_encode($oldMonthlyRestock) . " to " . json_encode($validated['monthly_restock'])
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Diaper budget updated successfully',
            'redirect' => route('budgets.index')
        ]);
    }

    /**
     * Delete diaper budget
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $budget = DiaperBudget::with('zone')->findOrFail($id);

        // Verify user owns this zone (skip for FC as they can manage all zones)
        if (!$user->hasRole('fc') && $budget->zone->user_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only delete budgets for your own zones'
            ], 403);
        }

        // Check if budget is being used by any active requests
        $hasDiapersRequests = $budget->diapersRequests()
            ->whereHas('requestHeader', function($q) {
                $q->notCancelled();
            })->count() > 0;

        if ($hasDiapersRequests) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete diaper budget that is being used by requests'
            ], 400);
        }

        // Soft delete by setting cancelled flag
        $budget->update(['cancelled' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Diaper budget deleted successfully'
        ]);
    }

    /**
     * Get diaper budgets for a specific zone (AJAX)
     * Used when HOR or FC is approving a request
     */
    public function getBudgetsForZone($zoneId)
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

        $budgets = DiaperBudget::notCancelled()->where('zone_id', $zoneId)->get();

        return response()->json([
            'success' => true,
            'budgets' => $budgets
        ]);
    }

    /**
     * Get diaper budget preview for a request (AJAX)
     * Shows current stock and preview after deduction
     */
    public function getBudgetPreview(Request $request)
    {
        $validated = $request->validate([
            'budget_id' => 'required|exists:diaper_budgets,id',
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:0',
            'ready_date' => 'required|date'
        ]);

        $budget = DiaperBudget::findOrFail($validated['budget_id']);
        $readyDate = Carbon::parse($validated['ready_date']);
        $year = $readyDate->year;
        $month = $readyDate->month;

        $currentStock = $budget->getRemainingStockForMonth($year, $month);
        $previewStock = $budget->getPreviewStockAfterRequest($validated['quantities'], $year, $month);
        $hasEnough = $budget->hasEnoughStock($validated['quantities'], $year, $month);

        return response()->json([
            'success' => true,
            'monthly_restock' => $budget->monthly_restock,
            'current_remaining' => $currentStock,
            'after_request' => $previewStock,
            'has_enough' => $hasEnough
        ]);
    }

    /**
     * Get all diaper budgets for user's zones (AJAX)
     * FC can see all zones, HOR can only see their own zones
     */
    public function getMyZoneBudgets()
    {
        $user = Auth::user();

        if ($user->hasRole('fc')) {
            $budgets = DiaperBudget::notCancelled()->get();
        } else {
            $budgets = DiaperBudget::notCancelled()->whereHas('zone', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();
        }

        return response()->json([
            'success' => true,
            'budgets' => $budgets
        ]);
    }
}
