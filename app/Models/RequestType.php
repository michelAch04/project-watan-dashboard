<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestType extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'format',
        'cancelled'
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
     * Scope to exclude cancelled request types
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }

    /**
     * Get type by name
     */
    public static function getByName($name)
    {
        return static::notCancelled()->where('name', $name)->first();
    }
}