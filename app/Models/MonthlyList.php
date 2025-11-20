<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyList extends Model
{
    protected $fillable = [
        'user_id',
        'request_id',
        'month',
        'year',
        'cancelled'
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer'
    ];

    /**
     * Get the user who owns this monthly list entry
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the request header in this monthly list
     */
    public function requestHeader()
    {
        return $this->belongsTo(RequestHeader::class, 'request_id');
    }

    /**
     * Scope to exclude cancelled items
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }

    /**
     * Scope to filter by month and year
     */
    public function scopeForMonth($query, $month, $year)
    {
        return $query->notCancelled()->where('month', $month)->where('year', $year);
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
