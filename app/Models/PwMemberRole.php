<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PwMemberRole extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'cancelled'
    ];

    protected $casts = [
        'cancelled' => 'boolean'
    ];

    /**
     * Get the PW members with this role
     */
    public function pwMembers()
    {
        return $this->hasMany(PwMember::class, 'pw_member_role_id');
    }

    /**
     * Scope to exclude cancelled roles
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }
}
