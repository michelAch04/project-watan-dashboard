<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RequestHeader extends Model
{
    protected $fillable = [
        'request_number',
        'request_date',
        'request_status_id',
        'reference_member_id',
        'ready_date',
        'sender_id',
        'current_user_id',
        'rejection_reason',
        'published_count',
        'cancelled'
    ];

    protected $casts = [
        'request_date' => 'date',
        'ready_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function requestStatus()
    {
        return $this->belongsTo(RequestStatus::class);
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

    public function humanitarianRequest()
    {
        return $this->hasOne(HumanitarianRequest::class);
    }

    public function publicRequest()
    {
        return $this->hasOne(PublicRequest::class);
    }

    public function diapersRequest()
    {
        return $this->hasOne(DiapersRequest::class);
    }

    public function inboxNotifications()
    {
        return $this->hasMany(InboxNotification::class);
    }

    /**
     * Scopes
     */
    public function scopeNotCancelled($query)
    {
        return $query->where('cancelled', 0);
    }

    public function scopeActive($query)
    {
        return $query->notCancelled()
            ->whereHas('requestStatus', function ($q) {
                $q->whereNotIn('name', [
                    RequestStatus::STATUS_DRAFT,
                    RequestStatus::STATUS_COLLECTED
                ]);
            });
    }

    public function scopeCompleted($query)
    {
        return $query->notCancelled()
            ->whereHas('requestStatus', function ($q) {
                $q->where('name', RequestStatus::STATUS_COLLECTED);
            });
    }

    public function scopeDraftsAndRejects($query, $user)
    {
        return $query->notCancelled()
            ->where('sender_id', $user->id)
            ->whereHas('requestStatus', function ($q) {
                $q->whereIn('name', [
                    RequestStatus::STATUS_DRAFT,
                    RequestStatus::STATUS_REJECTED
                ]);
            });
    }

    public function scopeForUser($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)
                ->orWhere('current_user_id', $user->id);

            // If user has a manager, include requests where user's subordinates are involved
            if ($user->hasRole('manager') || $user->hasRole('hor')) {
                $q->orWhereHas('sender', function ($subq) use ($user) {
                    $subq->where('manager_id', $user->id);
                });
            }
        });
    }

    /**
     * Helper methods
     */
    public static function generateRequestNumber()
    {
        $year = date('Y');
        $lastRequest = static::where('request_number', 'like', "REQ-{$year}-%")
            ->orderBy('request_number', 'desc')
            ->first();

        if ($lastRequest) {
            $lastNumber = intval(substr($lastRequest->request_number, -6));
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return "REQ-{$year}-{$newNumber}";
    }

    public function canEdit($user)
    {
        return $this->sender_id === $user->id &&
            in_array($this->requestStatus->name, [
                RequestStatus::STATUS_DRAFT,
                RequestStatus::STATUS_REJECTED
            ]);
    }

    public function canDelete($user)
    {
        return
            $this->sender_id === $user->id && (
                $this->requestStatus->name === RequestStatus::STATUS_DRAFT ||
                $this->requestStatus->name === RequestStatus::STATUS_REJECTED
            );
    }

    public function canApproveReject($user)
    {
        return $this->current_user_id === $user->id &&
            $this->requestStatus->name === RequestStatus::STATUS_PUBLISHED;
    }

    /**
     * Get the specific request type instance
     */
    public function getSpecificRequest()
    {
        if ($this->humanitarianRequest) {
            return $this->humanitarianRequest;
        } elseif ($this->publicRequest) {
            return $this->publicRequest;
        } elseif ($this->diapersRequest) {
            return $this->diapersRequest;
        }

        return null;
    }

    /**
     * Get request type name
     */
    public function getRequestType()
    {
        if ($this->humanitarianRequest) {
            return 'humanitarian';
        } elseif ($this->publicRequest) {
            return 'public';
        } elseif ($this->diapersRequest) {
            return 'diapers';
        }

        return 'unknown';
    }
}
