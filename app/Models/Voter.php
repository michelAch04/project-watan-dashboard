<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voter extends Model
{
    protected $table = 'voters_list';

    protected $fillable = [
        'first_name',
        'father_name',
        'last_name',
        'city_id',
        'ro_number',
        'phone',
        'cancelled'
    ];

    /**
     * Get the city
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get requests linked to this voter
     */
    public function requests()
    {
        return $this->hasMany(Request::class, 'voter_id');
    }

    /**
     * Get the PW member linked to this voter
     */
    public function pwMember()
    {
        return $this->hasOne(PwMember::class, 'voter_id');
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->father_name} {$this->last_name}";
    }

    /**
     * Scope to exclude cancelled voters
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }

    /**
     * Search scope
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('father_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('ro_number', 'like', "%{$search}%");
        });
    }
}