<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiapersRequest extends Model
{
    protected $fillable = [
        'request_header_id',
        'voter_id',
        'budget_id',
        'notes'
    ];

    /**
     * Relationships
     */
    public function requestHeader()
    {
        return $this->belongsTo(RequestHeader::class);
    }

    public function voter()
    {
        return $this->belongsTo(Voter::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function items()
    {
        return $this->hasMany(DiapersRequestItem::class);
    }

    /**
     * Get requester full name from voter
     */
    public function getRequesterFullNameAttribute()
    {
        if ($this->voter) {
            return "{$this->voter->first_name} {$this->voter->father_name} {$this->voter->last_name}";
        }
        return 'N/A';
    }

    /**
     * Get total amount based on items (if pricing is added later)
     */
    public function getTotalAmountAttribute()
    {
        // For now, return 0 or calculate based on items if pricing is implemented
        return 0;
    }
}
