<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DiaperBudget extends Model
{
    protected $fillable = [
        'description',
        'monthly_restock',
        'current_stock',
        'auto_refill_day',
        'last_refill_date',
        'zone_id',
        'cancelled'
    ];

    protected $casts = [
        'monthly_restock' => 'array',
        'current_stock' => 'array',
        'auto_refill_day' => 'integer',
        'last_refill_date' => 'datetime',
    ];

    /**
     * Get the zone this budget belongs to
     */
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Get all diapers requests using this budget
     */
    public function diapersRequests()
    {
        return $this->hasMany(DiapersRequest::class);
    }

    /**
     * Get all transactions for this budget
     */
    public function transactions()
    {
        return $this->hasMany(DiaperBudgetTransaction::class);
    }

    /**
     * Helper method to add/subtract stock arrays
     * Example: addStock(['xl' => 10, 'l' => 5], ['xl' => -3, 'l' => 2]) = ['xl' => 7, 'l' => 7]
     */
    protected function addStock($stock1, $stock2)
    {
        $result = $stock1;
        foreach ($stock2 as $size => $quantity) {
            $result[$size] = ($result[$size] ?? 0) + $quantity;
        }
        return $result;
    }

    /**
     * Helper method to subtract stock arrays
     */
    protected function subtractStock($stock1, $stock2)
    {
        $result = $stock1;
        foreach ($stock2 as $size => $quantity) {
            $result[$size] = ($result[$size] ?? 0) - $quantity;
        }
        return $result;
    }

    /**
     * Check if we have enough stock for a request
     */
    public function hasEnoughStock($requiredStock, $year, $month)
    {
        $remainingStock = $this->getRemainingStockForMonth($year, $month);

        foreach ($requiredStock as $size => $quantity) {
            if (($remainingStock[$size] ?? 0) < $quantity) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate current month's remaining stock
     * For current month: returns current_stock (already deducted)
     * For future months: calculates based on allocated requests
     */
    public function getRemainingStockForMonth($year, $month)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // If querying current month, return current_stock (already has deductions)
        if ($year == $currentYear && $month == $currentMonth) {
            return $this->current_stock;
        }

        // For future months, calculate based on allocations
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Start with monthly restock amount
        $remainingStock = $this->monthly_restock;

        // Get all diaper requests allocated for this month
        $allocatedRequests = DiapersRequest::where('diaper_budget_id', $this->id)
            ->whereHas('requestHeader', function($q) use ($startDate, $endDate) {
                $q->notCancelled()
                    ->whereBetween('ready_date', [$startDate, $endDate])
                    ->whereHas('requestStatus', function($q2) {
                        $q2->whereIn('name', [
                            RequestStatus::STATUS_FINAL_APPROVAL,
                            RequestStatus::STATUS_READY_FOR_COLLECTION,
                            RequestStatus::STATUS_COLLECTED
                        ]);
                    });
            })
            ->get();

        // Subtract all allocated quantities
        foreach ($allocatedRequests as $request) {
            if ($request->quantities) {
                $remainingStock = $this->subtractStock($remainingStock, $request->quantities);
            }
        }

        return $remainingStock;
    }

    /**
     * Get the stock that will remain if a request with given quantities is approved for given month
     */
    public function getPreviewStockAfterRequest($quantities, $year, $month)
    {
        return $this->subtractStock($this->getRemainingStockForMonth($year, $month), $quantities);
    }

    /**
     * Scope to exclude cancelled budgets
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }

    /**
     * Scope to filter budgets by zone
     */
    public function scopeForZone($query, $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    /**
     * Check if budget needs refill and perform it if necessary
     */
    public function checkAndRefill()
    {
        $today = Carbon::today();
        $refillDay = min($this->auto_refill_day, 28); // Ensure day is between 1-28

        // Check if we're on or past the refill day for this month
        $shouldRefill = false;

        if ($this->last_refill_date == null) {
            // Never refilled before
            $shouldRefill = true;
        } else {
            // Check if we've passed the refill day and haven't refilled this month yet
            $lastRefillMonth = $this->last_refill_date->format('Y-m');
            $currentMonth = $today->format('Y-m');

            if ($lastRefillMonth != $currentMonth && $today->day >= $refillDay) {
                $shouldRefill = true;
            }
        }

        if ($shouldRefill) {
            $this->refillBudget();
        }
    }

    /**
     * Perform budget refill
     * Also processes pending allocations for the new month
     */
    protected function refillBudget()
    {
        $today = Carbon::today();
        $currentMonth = $today->month;
        $currentYear = $today->year;

        // First, convert any pending allocations for this month to deductions
        $this->processPendingAllocations($currentYear, $currentMonth);

        // Then refill the stock
        $oldStock = $this->current_stock;
        $this->current_stock = $this->monthly_restock;
        $this->last_refill_date = $today;
        $this->save();

        // Record refill transaction
        DiaperBudgetTransaction::create([
            'diaper_budget_id' => $this->id,
            'type' => 'refill',
            'quantity_change' => $this->monthly_restock,
            'stock_after' => $this->current_stock,
            'description' => 'Monthly automatic refill'
        ]);

        // After refill, deduct the quantities that were allocated for this month
        $this->deductCurrentMonthAllocations($currentYear, $currentMonth);
    }

    /**
     * Process pending allocations - convert allocation transactions to deduction transactions
     * This is called during refill to mark allocations as processed
     */
    protected function processPendingAllocations($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Find all allocation transactions for requests with ready_date in this month
        $allocations = DiaperBudgetTransaction::where('diaper_budget_id', $this->id)
            ->where('type', 'allocation')
            ->whereHas('requestHeader', function($q) use ($startDate, $endDate) {
                $q->whereBetween('ready_date', [$startDate, $endDate])
                  ->notCancelled();
            })
            ->get();

        foreach ($allocations as $allocation) {
            // Update the allocation transaction to mark it as processed
            $allocation->update([
                'type' => 'allocation_processed',
                'description' => $allocation->description . ' (Processed on ' . now()->format('Y-m-d') . ')',
                'updated_at' => now()
            ]);
        }
    }

    /**
     * Deduct quantities for current month allocations after refill
     */
    protected function deductCurrentMonthAllocations($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get all diaper requests with ready_date in current month that were previously allocated
        $diapersRequests = DiapersRequest::where('diaper_budget_id', $this->id)
            ->whereHas('requestHeader', function($q) use ($startDate, $endDate) {
                $q->notCancelled()
                  ->whereBetween('ready_date', [$startDate, $endDate])
                  ->whereHas('requestStatus', function($q2) {
                        $q2->whereIn('name', [
                            RequestStatus::STATUS_FINAL_APPROVAL,
                            RequestStatus::STATUS_READY_FOR_COLLECTION,
                            RequestStatus::STATUS_COLLECTED
                        ]);
                    });
            })
            ->get();

        foreach ($diapersRequests as $request) {
            // Check if there's already a deduction for this request
            $hasDeduction = DiaperBudgetTransaction::where('diaper_budget_id', $this->id)
                ->where('request_id', $request->requestHeader->id)
                ->where('type', 'deduction')
                ->exists();

            if (!$hasDeduction && $request->quantities) {
                // Deduct from current stock
                $this->current_stock = $this->subtractStock($this->current_stock, $request->quantities);
                $this->save();

                // Create negative quantity change for transaction
                $quantityChange = [];
                foreach ($request->quantities as $size => $qty) {
                    $quantityChange[$size] = -$qty;
                }

                // Create deduction transaction
                DiaperBudgetTransaction::create([
                    'diaper_budget_id' => $this->id,
                    'type' => 'deduction',
                    'quantity_change' => $quantityChange,
                    'stock_after' => $this->current_stock,
                    'request_id' => $request->requestHeader->id,
                    'description' => "Deduction for request #{$request->requestHeader->request_number} (allocated for " . Carbon::create($year, $month)->format('F Y') . ")"
                ]);
            }
        }
    }

    /**
     * Allocate stock from budget based on ready_date
     * If ready_date is current month: deduct immediately from current_stock
     * If ready_date is future month: only track allocation (deduct when month arrives)
     */
    public function allocateForRequest($quantities, $readyDate, $requestId, $description = null)
    {
        // Check and refill if needed before allocating
        $this->checkAndRefill();

        $readyDateCarbon = Carbon::parse($readyDate);
        $readyMonth = $readyDateCarbon->month;
        $readyYear = $readyDateCarbon->year;

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Check if we have enough stock for the specified month
        if (!$this->hasEnoughStock($quantities, $readyYear, $readyMonth)) {
            $remaining = $this->getRemainingStockForMonth($readyYear, $readyMonth);
            $remainingStr = json_encode($remaining);
            throw new \Exception("Insufficient stock for " . $readyDateCarbon->format('F Y') . ". Remaining: " . $remainingStr);
        }

        // Create negative quantity change for deduction
        $quantityChange = [];
        foreach ($quantities as $size => $qty) {
            $quantityChange[$size] = -$qty;
        }

        // If ready_date is current month, deduct immediately from current_stock
        if ($readyYear == $currentYear && $readyMonth == $currentMonth) {
            $this->current_stock = $this->subtractStock($this->current_stock, $quantities);
            $this->save();

            // Record deduction transaction
            DiaperBudgetTransaction::create([
                'diaper_budget_id' => $this->id,
                'type' => 'deduction',
                'quantity_change' => $quantityChange,
                'stock_after' => $this->current_stock,
                'request_id' => $requestId,
                'description' => $description ?? "Request allocated and deducted from current stock"
            ]);
        } else {
            // If ready_date is future month, only record allocation (don't deduct yet)
            DiaperBudgetTransaction::create([
                'diaper_budget_id' => $this->id,
                'type' => 'allocation',
                'quantity_change' => $quantityChange,
                'stock_after' => $this->current_stock, // No change to stock for allocations
                'request_id' => $requestId,
                'description' => $description ?? "Allocated for " . $readyDateCarbon->format('F Y') . " (will be deducted on refill)"
            ]);
        }

        return $this;
    }

    /**
     * Adjust stock manually (for edits or corrections)
     */
    public function adjust($quantities, $description)
    {
        $this->current_stock = $this->addStock($this->current_stock, $quantities);
        $this->save();

        // Record transaction
        DiaperBudgetTransaction::create([
            'diaper_budget_id' => $this->id,
            'type' => 'adjustment',
            'quantity_change' => $quantities,
            'stock_after' => $this->current_stock,
            'description' => $description
        ]);

        return $this;
    }
}
