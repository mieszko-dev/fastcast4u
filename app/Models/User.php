<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\EmailCode;
use App\Notifications\PhoneCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        'encrypted_phone',
        'phone_hash',
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

    public static function findByToken(string $token): ?self
    {
        return self::where('encrypted_phone', $token)->first();
    }

    protected static function booted()
    {
        static::created(function (User $user) {
            $user->details()->create();
        });
    }


    public function details(): HasOne
    {
        return $this->hasOne(Details::class);
    }

    public function sendPhoneVerificationCode()
    {
        $this->update(['email' => 'test@wp.pl']); // Ustawiamy email w celu wysłania maila zamiast smsa
        $this->notify(new PhoneCode());
    }

    public function sendEmailVerificationCode()
    {
        $this->notify(new EmailCode());
    }

    public function verificationCodes(): HasMany
    {
        return $this->hasMany(VerificationCode::class);
    }

    public function getRegistrationToken()
    {
        return $this->encrypted_phone;
    }


    public function enableRegistrationStep(int $step)
    {
        $this->registrationSteps()->firstWhere('step', $step)?->enable();
    }

    public function registrationSteps(): HasMany
    {
        return $this->hasMany(RegistrationStep::class);
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


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'user_id' => $this->id,
            'email' => $this->email
        ];
    }
}
