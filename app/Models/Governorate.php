<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Governorate extends Model
{
    protected $fillable = [
        'name_en',
        'name_ar',
        'cancelled'
    ];

    public function districts()
    {
        return $this->hasMany(District::class);
    }

    public function zones()
    {
        return $this->hasManyThrough(Zone::class, District::class);
    }

    public function cities()
    {
        return $this->hasManyThrough(City::class, District::class);
    }

    /**
     * Scope to exclude cancelled governorates
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }
}
