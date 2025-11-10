<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestType extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'format'
    ];

    /**
     * Get requests of this type
     */
    public function requests()
    {
        return $this->hasMany(Request::class, 'request_type_id');
    }

    /**
     * Constants for request types
     */
    const TYPE_PUBLIC = 'public';
    const TYPE_HUMANITARIAN = 'humanitarian';
    const TYPE_DIAPERS = 'diapers';
    const TYPE_OTHERS = 'others';

    /**
     * Get type by name
     */
    public static function getByName($name)
    {
        return static::where('name', $name)->first();
    }
}