<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = [
        'name_en',
        'name_ar',
        'district_id',
        'user_id',
        'cancelled'
    ];
    
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class);
    }

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function diaperBudgets()
    {
        return $this->hasMany(DiaperBudget::class);
    }

    /**
     * Scope to exclude cancelled zones
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }
}
