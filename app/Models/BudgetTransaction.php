<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetTransaction extends Model
{
    protected $fillable = [
        'budget_id',
        'type',
        'amount',
        'balance_after',
        'request_id',
        'description',
        'cancelled'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relationships
    public function budget()
    {
        return $this->belongsTo(Budget::class);
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
