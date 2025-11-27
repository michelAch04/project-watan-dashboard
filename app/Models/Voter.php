<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voter extends Model
{
    protected $table = 'voters_list';

    protected $fillable = [
        'first_name',
        'father_name',
        'last_name',
        'mother_full_name',
        'city_id',
        'register_number',
        'phone',
        'cancelled'
    ];

    /**
     * Get the city
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get humanitarian requests linked to this voter
     */
    public function humanitarianRequests()
    {
        return $this->hasMany(HumanitarianRequest::class, 'voter_id');
    }

    /**
     * Get diapers requests linked to this voter
     */
    public function diapersRequests()
    {
        return $this->hasMany(DiapersRequest::class, 'voter_id');
    }

    /**
     * Get the PW member linked to this voter
     */
    public function pwMember()
    {
        return $this->hasOne(PwMember::class, 'voter_id');
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->father_name} {$this->last_name}";
    }

    /**
     * Scope to exclude cancelled voters
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }

    /**
     * Search scope
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('father_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('register_number', 'like', "%{$search}%");
        });
    }

    /**
     * Check if voter has any rejected requests (humanitarian or diapers)
     */
    public function hasRejectedRequests()
    {
        $hasRejectedHumanitarian = $this->humanitarianRequests()
            ->whereHas('requestHeader', function($q) {
                $q->notCancelled()
                    ->whereHas('requestStatus', function($q2) {
                        $q2->where('name', RequestStatus::STATUS_REJECTED);
                    });
            })
            ->exists();

        $hasRejectedDiapers = $this->diapersRequests()
            ->whereHas('requestHeader', function($q) {
                $q->notCancelled()
                    ->whereHas('requestStatus', function($q2) {
                        $q2->where('name', RequestStatus::STATUS_REJECTED);
                    });
            })
            ->exists();

        return $hasRejectedHumanitarian || $hasRejectedDiapers;
    }

    /**
     * Get all rejected requests (humanitarian and diapers)
     */
    public function getRejectedRequests()
    {
        $rejectedHumanitarian = $this->humanitarianRequests()
            ->whereHas('requestHeader', function($q) {
                $q->notCancelled()
                    ->whereHas('requestStatus', function($q2) {
                        $q2->where('name', RequestStatus::STATUS_REJECTED);
                    });
            })
            ->with('requestHeader')
            ->get();

        $rejectedDiapers = $this->diapersRequests()
            ->whereHas('requestHeader', function($q) {
                $q->notCancelled()
                    ->whereHas('requestStatus', function($q2) {
                        $q2->where('name', RequestStatus::STATUS_REJECTED);
                    });
            })
            ->with('requestHeader')
            ->get();

        return $rejectedHumanitarian->concat($rejectedDiapers);
    }
}