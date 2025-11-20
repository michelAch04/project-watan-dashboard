<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestStatus extends Model
{
    protected $table = 'request_statuses';

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'order',
        'cancelled'
    ];

    /**
     * Get requests with this status
     */
    public function requests()
    {
        return $this->hasMany(Request::class, 'request_status_id');
    }

    /**
     * Constants for statuses
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_FINAL_APPROVAL = 'final_approval';
    const STATUS_READY_FOR_COLLECTION = 'ready_for_collection';
    const STATUS_COLLECTED = 'collected';

    /**
     * Scope to exclude cancelled request statuses
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }

    /**
     * Get status by name
     */
    public static function getByName($name)
    {
        return static::notCancelled()->where('name', $name)->first();
    }
}