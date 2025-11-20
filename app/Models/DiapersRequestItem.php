<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiapersRequestItem extends Model
{
    protected $fillable = [
        'diapers_request_id',
        'size',
        'count'
    ];

    protected $casts = [
        'count' => 'integer',
    ];

    /**
     * Relationships
     */
    public function diapersRequest()
    {
        return $this->belongsTo(DiapersRequest::class);
    }

    /**
     * Common diaper sizes
     */
    public static function getAvailableSizes()
    {
        return [
            'newborn' => 'Newborn',
            'xs' => 'XS',
            's' => 'S',
            'm' => 'M',
            'l' => 'L',
            'xl' => 'XL',
            'xxl' => 'XXL',
        ];
    }
}
