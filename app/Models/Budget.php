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
     * Get all requests using this budget
     */
    public function requests()
    {
        return $this->hasMany(Request::class);
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
     */
    public function getRemainingBudgetForMonth($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get all requests that:
        // 1. Use this budget
        // 2. Have ready_date in the specified month
        // 3. Are in final_approval or later status (actually deducted from budget)
        $totalUsed = Request::notCancelled()
            ->where('budget_id', $this->id)
            ->whereBetween('ready_date', [$startDate, $endDate])
            ->whereHas('requestStatus', function($q) {
                $q->whereIn('name', [
                    RequestStatus::STATUS_FINAL_APPROVAL,
                    RequestStatus::STATUS_READY_FOR_COLLECTION,
                    RequestStatus::STATUS_COLLECTED
                ]);
            })
            ->sum('amount');

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
        $totalPredicted = Request::notCancelled()
            ->where('budget_id', $this->id)
            ->whereBetween('ready_date', [$startDate, $endDate])
            ->whereHas('requestStatus', function($q) {
                $q->whereIn('name', [
                    RequestStatus::STATUS_FINAL_APPROVAL,
                    RequestStatus::STATUS_READY_FOR_COLLECTION,
                    RequestStatus::STATUS_COLLECTED
                ]);
            })
            ->sum('amount');

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
     */
    protected function refillBudget()
    {
        $oldBalance = $this->current_balance;
        $this->current_balance = $this->monthly_amount_in_usd;
        $this->last_refill_date = Carbon::today();
        $this->save();

        // Record transaction
        BudgetTransaction::create([
            'budget_id' => $this->id,
            'type' => 'refill',
            'amount' => $this->monthly_amount_in_usd,
            'balance_after' => $this->current_balance,
            'description' => 'Monthly automatic refill'
        ]);
    }

    /**
     * Deduct amount from budget (for request allocation)
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
