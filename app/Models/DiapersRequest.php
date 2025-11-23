<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiapersRequest extends Model
{
    protected $fillable = [
        'request_header_id',
        'voter_id',
        'diaper_budget_id',
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

    public function diaperBudget()
    {
        return $this->belongsTo(DiaperBudget::class);
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

    /**
     * Get quantities array from items
     * Returns array like ['xl' => 2, 'm' => 3]
     */
    public function getQuantitiesAttribute()
    {
        $quantities = [];
        foreach ($this->items as $item) {
            $quantities[strtolower($item->size)] = $item->count;
        }
        return $quantities;
    }

    /**
     * Get total quantity count
     */
    public function getTotalQuantityAttribute()
    {
        return $this->items()->sum('count');
    }
}
