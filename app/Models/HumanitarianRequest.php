<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HumanitarianRequest extends Model
{
    protected $fillable = [
        'request_header_id',
        'voter_id',
        'subtype',
        'amount',
        'budget_id',
        'notes',
        'supporting_documents'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'supporting_documents' => 'array',
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
     * Get requester city from voter
     */
    public function getRequesterCityAttribute()
    {
        return $this->voter ? $this->voter->city : null;
    }

    /**
     * Get requester RO number from voter
     */
    public function getRequesterRoNumberAttribute()
    {
        return $this->voter ? $this->voter->ro_number : null;
    }

    /**
     * Get requester phone from voter
     */
    public function getRequesterPhoneAttribute()
    {
        return $this->voter ? $this->voter->phone : null;
    }
}
