<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicRequest extends Model
{
    protected $fillable = [
        'request_header_id',
        'public_institution_id',
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

    public function publicInstitution()
    {
        return $this->belongsTo(PublicInstitution::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}
