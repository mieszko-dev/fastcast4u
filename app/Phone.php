<?php

namespace App;

use Illuminate\Support\Facades\Crypt;

class Phone
{
    public static function encrypt(string $phone): string
    {
        return Crypt::encryptString($phone);
    }

    public static function hash(string $phone): string
    {
        return hash_hmac('sha256', $phone, config('app.key'));
    }


}
