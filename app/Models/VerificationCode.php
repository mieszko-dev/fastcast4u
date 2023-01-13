<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationCode extends Model
{
    use HasFactory;

    const PHONE = 'phone';
    const EMAIL = 'email';

    protected $guarded = [];

    protected $casts = [
        'activated' => 'boolean'
    ];

    public static function createPhoneCodeForUser(User $user)
    {
        return self::generateCodeForUser($user, self::PHONE);
    }

    private static function generateCodeForUser(User $user, string $type)
    {
        self::where('type', $type)->delete();

        return self::create([
            'code' => self::generateCode(),
            'user_id' => $user->id,
            'type' => $type
        ]);
    }

    private static function generateCode(): int
    {
        return intval(random_int(1, 9) . random_int(10000, 99999));
    }

    public static function createEmailCodeForUser(User $user)
    {
        return self::generateCodeForUser($user, self::EMAIL);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


}
