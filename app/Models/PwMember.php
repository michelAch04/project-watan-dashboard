<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PwMember extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'voter_id',
        'is_active',
        'cancelled'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get the user account linked to this PW member
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'pw_member_id');
    }

    /**
     * Get the voter linked to this PW member
     */
    public function voter()
    {
        return $this->belongsTo(Voter::class);
    }

    /**
     * Get requests where this member is referenced
     */
    public function requests()
    {
        return $this->hasMany(Request::class, 'reference_member_id');
    }

    /**
     * Scope to exclude cancelled members
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }

    /**
     * Scope for active members only
     */
    public function scopeActive($query)
    {
        return $query->notCancelled()->where('is_active', true);
    }
}