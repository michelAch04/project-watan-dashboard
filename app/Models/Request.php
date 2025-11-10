<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Request extends Model
{
    protected $fillable = [
        'request_number',
        'request_date',
        'request_type_id',
        'request_status_id',
        'requester_first_name',
        'requester_father_name',
        'requester_last_name',
        'requester_city_id',
        'requester_ro_number',
        'requester_phone',
        'voter_id',
        'public_institution_id',
        'subtype',
        'reference_member_id',
        'amount',
        'notes',
        'sender_id',
        'current_user_id',
        'rejection_reason'
    ];

    protected $casts = [
        'request_date' => 'date',
        'amount' => 'decimal:2'
    ];

    /**
     * Boot method to generate request number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->request_number)) {
                $request->request_number = static::generateRequestNumber();
            }
            if (empty($request->request_date)) {
                $request->request_date = now();
            }
        });
    }

    /**
     * Generate unique request number
     */
    public static function generateRequestNumber()
    {
        $year = date('Y');
        $lastRequest = static::where('request_number', 'like', "REQ-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastRequest) {
            $lastNumber = intval(substr($lastRequest->request_number, -6));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf("REQ-%s-%06d", $year, $newNumber);
    }

    /**
     * Relationships
     */
    public function requestType()
    {
        return $this->belongsTo(RequestType::class);
    }

    public function requestStatus()
    {
        return $this->belongsTo(RequestStatus::class);
    }

    public function requesterCity()
    {
        return $this->belongsTo(City::class, 'requester_city_id');
    }

    public function voter()
    {
        return $this->belongsTo(Voter::class);
    }

    public function publicInstitution()
    {
        return $this->belongsTo(PublicInstitution::class);
    }

    public function referenceMember()
    {
        return $this->belongsTo(PwMember::class, 'reference_member_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function currentUser()
    {
        return $this->belongsTo(User::class, 'current_user_id');
    }

    public function inboxNotifications()
    {
        return $this->hasMany(InboxNotification::class);
    }

    /**
     * Get requester full name
     */
    public function getRequesterFullNameAttribute()
    {
        if ($this->voter) {
            return $this->voter->full_name;
        }
        return trim("{$this->requester_first_name} {$this->requester_father_name} {$this->requester_last_name}");
    }

    /**
     * Check if user can edit
     */
    public function canEdit(User $user)
    {
        return $this->sender_id === $user->id && 
               in_array($this->requestStatus->name, [RequestStatus::STATUS_DRAFT, RequestStatus::STATUS_REJECTED]);
    }

    /**
     * Check if user can approve/reject
     */
    public function canApproveReject(User $user)
    {
        return $this->current_user_id === $user->id && 
               $this->requestStatus->name === RequestStatus::STATUS_PUBLISHED;
    }

    /**
     * Scopes
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where(function($q) use ($user) {
            $q->where('sender_id', $user->id)
              ->orWhere('current_user_id', $user->id)
              ->orWhereHas('sender', function($sq) use ($user) {
                  $sq->where('manager_id', $user->id);
              });
        });
    }

    public function scopeActive($query)
    {
        return $query->whereHas('requestStatus', function($q) {
            $q->where('name', '!=', RequestStatus::STATUS_COLLECTED)
                ->where('name', '!=', RequestStatus::STATUS_DRAFT);
        });
    }

    public function scopeCompleted($query)
    {
        return $query->whereHas('requestStatus', function($q) {
            $q->where('name', RequestStatus::STATUS_COLLECTED);
        });
    }

    public function scopeDraftsAndRejects($query, User $user)
    {
        return $query->where('sender_id', $user->id)
            ->whereHas('requestStatus', function($q) {
                $q->whereIn('name', [RequestStatus::STATUS_DRAFT, RequestStatus::STATUS_REJECTED]);
            });
    }

    public function scopeOfType($query, $typeName)
    {
        return $query->whereHas('requestType', function($q) use ($typeName) {
            $q->where('name', $typeName);
        });
    }
}