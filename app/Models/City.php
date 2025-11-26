<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class City extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'zone_id',
        'user_id',
        'cancelled'
    ];

    protected $casts = [
        'user_id' => 'array'
    ];

    /**
     * Get all managers assigned to this city
     * Returns users whose IDs are in the JSON user_id array
     */
    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            null,
            null,
            'id',
            'user_id'
        )->whereRaw('FIND_IN_SET(users.id, JSON_EXTRACT(cities.user_id, "$"))');
    }

    /**
     * Get the zone this city belongs to
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    /**
     * Check if a user is assigned to this city
     */
    public function hasUser($userId): bool
    {
        if (!$this->user_id) {
            return false;
        }
        return in_array($userId, $this->user_id);
    }

    /**
     * Assign a user to this city (add to JSON array if not already present)
     */
    public function assignUser($userId): void
    {
        $users = $this->user_id ?? [];
        if (!in_array($userId, $users)) {
            $users[] = $userId;
            $this->update(['user_id' => $users]);
        }
    }

    /**
     * Remove a user from this city (remove from JSON array)
     */
    public function removeUser($userId): void
    {
        $users = $this->user_id ?? [];
        $users = array_filter($users, function($id) use ($userId) {
            return $id != $userId;
        });
        $this->update(['user_id' => array_values($users)]);
    }

    /**
     * Clear all users from this city
     */
    public function clearUsers(): void
    {
        $this->update(['user_id' => null]);
    }

    /**
     * Scope to exclude cancelled cities
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }
}
