<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VerificationCode extends Model
{
    use HasFactory;

    const PHONE = 'phone';
    const EMAIL = 'email';

    protected $guarded = [];

    protected $casts = [
        'used' => 'boolean'
    ];


    protected static function booted()
    {
        static::creating(function ($user) {
            $user->code = self::generateCode();
        });
    }

    public static function generateCode(): int
    {
        $code = '';

        for ($i = 0; $i < 6; $i++) {
            $code .= mt_rand(1, 9);
        }

        return $code;
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


}
