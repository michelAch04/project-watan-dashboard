<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Village extends Model
{
    protected $fillable = [
        'name',
        'name_ar',
        'city_id',
        'user_ids',
        'cancelled'
    ];

    protected $casts = [
        'user_id' => 'array'
    ];

    /**
     * Get the city this village belongs to
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the manager assigned to this village
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if a user is assigned to this village
     */
    public function hasUser($userId): bool
    {
        if (!$this->user_id) {
            return false;
        }
        return in_array($userId, $this->user_id);
    }

    /**
     * Assign a user to this village (add to JSON array if not already present)
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
     * Remove a user from this village (remove from JSON array)
     */
    public function removeUser($userId): void
    {
        $users = $this->user_id ?? [];
        $users = array_filter($users, function($id) use ($userId) {
            return $id !== $userId;
        });
        $this->update(['user_id' => array_values($users)]);
    }

    /**
     * Clear all users from this village
     */
    public function clearUsers(): void
    {
        $this->update(['user_id' => null]);
    }
}
