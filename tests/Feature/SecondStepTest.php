<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class SecondStepTest extends TestCase
{
    private string $url = '/api/register/step2';

    public function test_requires_token_in_request()
    {
        $this->postJson($this->url)->assertUnprocessable()
            ->assertJsonValidationErrorFor('registration_token');
    }

    public function test_requires_verification_code_in_request()
    {
        $this->postJson($this->url)->assertUnprocessable()
            ->assertJsonValidationErrorFor('verification_code');
    }

    public function test_it_validates_code()
    {
        $user = User::create([
            'phone' => Crypt::encryptString('+48123123123'),
            'ip' => '0.0.0.0'
        ]);

        $code = VerificationCode::create([
            'code' => 123,
            'user_id' => $user->id,
            'type' => 'phone'
        ]);

        $this->postJson($this->url, [
            'registration_token' => $user->phone,
            'verification_code' => $code->code,
        ])->assertOk();
    }

    public function test_it_returns_token_number()
    {
        $user = User::create([
            'phone' => Crypt::encryptString('+48123123123'),
            'ip' => '0.0.0.0'
        ]);

        $code = VerificationCode::create([
            'code' => 123,
            'user_id' => $user->id,
            'type' => 'phone'
        ]);

        $this->postJson($this->url, [
            'registration_token' => $user->phone,
            'verification_code' => $code->code,
        ])
            ->assertOk()
            ->assertJsonStructure(['registration_token']);


    }


}
