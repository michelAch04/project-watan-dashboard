<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicRequest extends Model
{
    protected $fillable = [
        'request_header_id',
        'city_id',
        'description',
        'requester_full_name',
        'requester_phone',
        'amount',
        'budget_id',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function requestHeader()
    {
        return $this->belongsTo(RequestHeader::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}
