<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'name',
        'name_ar', 
        'zone_id',
        'user_id'
    ];

    protected $casts = [
        'user_id' => 'array'
    ];

    public function managers()
    {
        return $this->belongsToMany(User::class, null, 'user_id', 'id')
            ->where(function ($query) {
                $query->whereJsonContains('cities.user_id', 'id');
            });
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
