<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'password',
        'phone',
        'ip'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($user) {
            $user->details()->create();
        });
    }

    public function details(): HasOne
    {
        return $this->hasOne(Details::class);
    }

    public function getRegistrationToken()
    {
        return $this->phone;
    }

    public function consentToPhoneMarketing()
    {
        $this->details->update([
            'phone_marketing_consent_at' => now(),
            'phone_marketing_consent' => true
        ]);
    }

    public function consentToEmailMarketing()
    {
        $this->details->update([
            'email_marketing_consent_at' => now(),
            'email_marketing_consent' => true
        ]);
    }

    public function isPhoneVerified()
    {
        return $this->verificationCodes()
            ->where(['type' => VerificationCode::PHONE, 'activated' => true])
            ->exists();
    }

    public function verificationCodes(): HasMany
    {
        return $this->hasMany(VerificationCode::class);
    }

    public function createPhoneCode(): VerificationCode
    {
        return VerificationCode::createPhoneCodeForUser($this);
    }

    public function markPhoneAsVerified(): void
    {
        $this->forceFill(['phone_verified_at' => now()])->save();
    }

    public function markEmailAsVerified(): void
    {
        $this->forceFill(['email_verified_at' => now()])->save();
    }

    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function hasVerifiedPhone(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
