<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PwMember extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get requests where this member is referenced
     */
    public function requests()
    {
        return $this->hasMany(Request::class, 'reference_member_id');
    }

    /**
     * Scope for active members only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}