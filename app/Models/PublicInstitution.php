<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicInstitution extends Model
{
    protected $fillable = [
        'name',
        'description',
        'city_id',
        'contact_person',
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
     * Get public requests for this institution
     */
    public function publicRequests()
    {
        return $this->hasMany(PublicRequest::class, 'public_institution_id');
    }

    /**
     * Scope to exclude cancelled institutions
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
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}