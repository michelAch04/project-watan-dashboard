<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamSupportRequest extends Model
{
    protected $fillable = [
        'request_header_id',
        'pw_member_id',
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

    public function pwMember()
    {
        return $this->belongsTo(PwMember::class);
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get requester full name from pw member
     */
    public function getRequesterFullNameAttribute()
    {
        if ($this->pwMember) {
            return "{$this->pwMember->first_name} {$this->pwMember->father_name} {$this->pwMember->last_name}";
        }
        return 'N/A';
    }

    /**
     * Get requester phone from pw member
     */
    public function getRequesterPhoneAttribute()
    {
        return $this->pwMember ? $this->pwMember->phone : null;
    }
}
