<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Budget extends Model
{
    protected $fillable = [
        'description',
        'monthly_amount_in_usd',
        'current_balance',
        'auto_refill_day',
        'last_refill_date',
        'zone_id',
        'cancelled'
    ];

    protected $casts = [
        'monthly_amount_in_usd' => 'integer',
        'current_balance' => 'integer',
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
     * Get all humanitarian requests using this budget
     */
    public function humanitarianRequests()
    {
        return $this->hasMany(HumanitarianRequest::class);
    }

    /**
     * Get all public requests using this budget
     */
    public function publicRequests()
    {
        return $this->hasMany(PublicRequest::class);
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
        return $this->hasMany(BudgetTransaction::class);
    }

    /**
     * Calculate current month's remaining budget
     * Takes into account all approved requests with ready_date in current month
     * For current month: returns current_balance (already deducted)
     * For future months: calculates based on allocated requests
     */
    public function getRemainingBudgetForMonth($year, $month)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // If querying current month, return current_balance (already has deductions)
        if ($year == $currentYear && $month == $currentMonth) {
            return $this->current_balance;
        }

        // For future months, calculate based on allocations
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get all request headers that:
        // 1. Have ready_date in the specified month
        // 2. Are in final_approval or later status (allocated to that month)
        // 3. Have humanitarian/public requests linked to this budget
        $totalUsed = 0;

        // Sum from humanitarian requests
        $humanitarianUsed = HumanitarianRequest::where('budget_id', $this->id)
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
            ->sum('amount');

        // Sum from public requests
        $publicUsed = PublicRequest::where('budget_id', $this->id)
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
            ->sum('amount');

        // Diapers requests don't have amount, so we skip them for now
        $totalUsed = $humanitarianUsed + $publicUsed;

        return $this->monthly_amount_in_usd - $totalUsed;
    }

    /**
     * Get predicted budget after month ends
     * This includes ALL approved requests with ready_date in current month (even if not yet marked as ready)
     */
    public function getPredictedBudgetForMonth($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get all requests with ready_date in the specified month that are approved or higher
        $totalPredicted = 0;

        // Sum from humanitarian requests
        $humanitarianPredicted = HumanitarianRequest::where('budget_id', $this->id)
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
            ->sum('amount');

        // Sum from public requests
        $publicPredicted = PublicRequest::where('budget_id', $this->id)
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
            ->sum('amount');

        $totalPredicted = $humanitarianPredicted + $publicPredicted;

        return $this->monthly_amount_in_usd - $totalPredicted;
    }

    /**
     * Get the amount that will be used if a request with given amount is approved for given month
     */
    public function getPreviewBudgetAfterRequest($amount, $year, $month)
    {
        return $this->getRemainingBudgetForMonth($year, $month) - $amount;
    }

    /**
     * Check if there's enough budget for a request
     */
    public function hasEnoughBudget($amount, $year, $month)
    {
        return $this->getRemainingBudgetForMonth($year, $month) >= $amount;
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

        if ($this->last_refill_date === null) {
            // Never refilled before
            $shouldRefill = true;
        } else {
            // Check if we've passed the refill day and haven't refilled this month yet
            $lastRefillMonth = $this->last_refill_date->format('Y-m');
            $currentMonth = $today->format('Y-m');

            if ($lastRefillMonth !== $currentMonth && $today->day >= $refillDay) {
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

        // Then refill the budget
        $oldBalance = $this->current_balance;
        $this->current_balance = $this->monthly_amount_in_usd;
        $this->last_refill_date = $today;
        $this->save();

        // Record refill transaction
        BudgetTransaction::create([
            'budget_id' => $this->id,
            'type' => 'refill',
            'amount' => $this->monthly_amount_in_usd,
            'balance_after' => $this->current_balance,
            'description' => 'Monthly automatic refill'
        ]);

        // After refill, deduct the amounts that were allocated for this month
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
        $allocations = BudgetTransaction::where('budget_id', $this->id)
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
     * Deduct amounts for current month allocations after refill
     */
    protected function deductCurrentMonthAllocations($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get all requests with ready_date in current month that were previously allocated
        $allocatedRequests = [];

        // Humanitarian requests
        $humanitarianRequests = HumanitarianRequest::where('budget_id', $this->id)
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

        foreach ($humanitarianRequests as $request) {
            // Check if there's already a deduction for this request
            $hasDeduction = BudgetTransaction::where('budget_id', $this->id)
                ->where('request_id', $request->requestHeader->id)
                ->where('type', 'deduction')
                ->exists();

            if (!$hasDeduction) {
                // Deduct from current balance
                $this->current_balance -= $request->amount;
                $this->save();

                // Create deduction transaction
                BudgetTransaction::create([
                    'budget_id' => $this->id,
                    'type' => 'deduction',
                    'amount' => -$request->amount,
                    'balance_after' => $this->current_balance,
                    'request_id' => $request->requestHeader->id,
                    'description' => "Deduction for request #{$request->requestHeader->request_number} (allocated for " . Carbon::create($year, $month)->format('F Y') . ")"
                ]);
            }
        }

        // Public requests
        $publicRequests = PublicRequest::where('budget_id', $this->id)
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

        foreach ($publicRequests as $request) {
            $hasDeduction = BudgetTransaction::where('budget_id', $this->id)
                ->where('request_id', $request->requestHeader->id)
                ->where('type', 'deduction')
                ->exists();

            if (!$hasDeduction) {
                $this->current_balance -= $request->amount;
                $this->save();

                BudgetTransaction::create([
                    'budget_id' => $this->id,
                    'type' => 'deduction',
                    'amount' => -$request->amount,
                    'balance_after' => $this->current_balance,
                    'request_id' => $request->requestHeader->id,
                    'description' => "Deduction for request #{$request->requestHeader->request_number} (allocated for " . Carbon::create($year, $month)->format('F Y') . ")"
                ]);
            }
        }
    }

    /**
     * Allocate amount from budget based on ready_date
     * If ready_date is current month: deduct immediately from current_balance
     * If ready_date is future month: only track allocation (deduct when month arrives)
     */
    public function allocateForRequest($amount, $readyDate, $requestId, $description = null)
    {
        // Check and refill if needed before allocating
        $this->checkAndRefill();

        $readyDateCarbon = Carbon::parse($readyDate);
        $readyMonth = $readyDateCarbon->month;
        $readyYear = $readyDateCarbon->year;

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Check if we have enough budget for the specified month
        if (!$this->hasEnoughBudget($amount, $readyYear, $readyMonth)) {
            $remaining = $this->getRemainingBudgetForMonth($readyYear, $readyMonth);
            throw new \Exception("Insufficient budget for " . $readyDateCarbon->format('F Y') . ". Remaining: $" . number_format($remaining, 2));
        }

        // If ready_date is current month, deduct immediately from current_balance
        if ($readyYear == $currentYear && $readyMonth == $currentMonth) {
            $this->current_balance -= $amount;
            $this->save();

            // Record deduction transaction
            BudgetTransaction::create([
                'budget_id' => $this->id,
                'type' => 'deduction',
                'amount' => -$amount,
                'balance_after' => $this->current_balance,
                'request_id' => $requestId,
                'description' => $description ?? "Request allocated and deducted from current balance"
            ]);
        } else {
            // If ready_date is future month, only record allocation (don't deduct yet)
            // balance_after stays the same as current_balance since no deduction happened yet
            BudgetTransaction::create([
                'budget_id' => $this->id,
                'type' => 'allocation',
                'amount' => -$amount,
                'balance_after' => $this->current_balance, // No change to balance for allocations
                'request_id' => $requestId,
                'description' => $description ?? "Allocated for " . $readyDateCarbon->format('F Y') . " (will be deducted on refill)"
            ]);
        }

        return $this;
    }

    /**
     * Deduct amount from budget (for request allocation)
     * @deprecated Use allocateForRequest() instead
     */
    public function deduct($amount, $requestId = null, $description = null)
    {
        // Check and refill if needed before deducting
        $this->checkAndRefill();

        if ($this->current_balance < $amount) {
            throw new \Exception('Insufficient budget balance');
        }

        $this->current_balance -= $amount;
        $this->save();

        // Record transaction
        BudgetTransaction::create([
            'budget_id' => $this->id,
            'type' => 'deduction',
            'amount' => -$amount, // Negative for deduction
            'balance_after' => $this->current_balance,
            'request_id' => $requestId,
            'description' => $description ?? 'Budget allocation for request'
        ]);

        return $this;
    }

    /**
     * Adjust budget manually (for edits or corrections)
     */
    public function adjust($amount, $description)
    {
        $this->current_balance += $amount;
        $this->save();

        // Record transaction
        BudgetTransaction::create([
            'budget_id' => $this->id,
            'type' => 'adjustment',
            'amount' => $amount,
            'balance_after' => $this->current_balance,
            'description' => $description
        ]);

        return $this;
    }
}
