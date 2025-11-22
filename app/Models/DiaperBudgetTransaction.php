<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaperBudgetTransaction extends Model
{
    protected $fillable = [
        'diaper_budget_id',
        'type',
        'quantity_change',
        'stock_after',
        'request_id',
        'description',
        'cancelled'
    ];

    protected $casts = [
        'quantity_change' => 'array',
        'stock_after' => 'array',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function diaperBudget()
    {
        return $this->belongsTo(DiaperBudget::class);
    }

    public function requestHeader()
    {
        return $this->belongsTo(RequestHeader::class, 'request_id');
    }

    // Scopes
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('created_at', $year)
                     ->whereMonth('created_at', $month);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
