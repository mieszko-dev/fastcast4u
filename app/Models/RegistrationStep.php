<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RegistrationStep extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        'enabled' => 'boolean'
    ];


    public static function createSteps(User $user)
    {
        $steps = [];
        for ($i = 2; $i <= 4; $i++) {
            $steps[] = [
                'step' => $i,
                'user_id' => $user->id,
            ];
        }

        return $user->registrationSteps()->createMany($steps);
    }


    public function enable()
    {
        $this->update(['enabled' => true]);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
