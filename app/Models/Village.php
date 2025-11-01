<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{
    protected $fillable = [
        'name',
        'name_ar', 
        'city_id',
        'user_id'
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class);
    }
}
