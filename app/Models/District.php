<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'governorate_id',
        'cancelled'
    ];

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    /**
     * Scope to exclude cancelled districts
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }
}
