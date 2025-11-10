<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pw_member_id',
        'name',
        'email',
        'mobile',
        'password',
        'manager_id',
        'otp_code',
        'otp_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the PW member this user belongs to
     */
    public function pwMember()
    {
        return $this->belongsTo(PwMember::class, 'pw_member_id');
    }

    /**
     * Get the user's primary role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function role()
    {
        return $this->roles()->first();
    }
    
    public function zones()
    {
        return $this->hasMany(Zone::class);
    }

    public function cities()
    {
        // Cities where user_id JSON array contains this user's ID
        return City::whereJsonContains('user_id', $this->id)->get();
    }

    public function villages()
    {
        // Villages where user_id JSON array contains this user's ID
        return Village::whereJsonContains('user_id', $this->id)->get();
    }

        /**
     * Generate and save OTP
     */
    public function generateOTP()
    {
        $this->otp_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->otp_expires_at = Carbon::now()->addMinutes(5);
        $this->save();
        
        return $this->otp_code;
    }

    /**
     * Verify OTP code
     */
    public function verifyOTP($code)
    {
        if ($this->otp_code === $code && 
            $this->otp_expires_at && 
            Carbon::now()->lessThan($this->otp_expires_at)) {
            
            // Clear OTP after successful verification
            $this->otp_code = null;
            $this->otp_expires_at = null;
            $this->save();
            
            return true;
        }
        
        return false;
    }

    /**
     * Check if OTP is expired
     */
    public function isOTPExpired()
    {
        return !$this->otp_expires_at || Carbon::now()->greaterThan($this->otp_expires_at);
    }
}