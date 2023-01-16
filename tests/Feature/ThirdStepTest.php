<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class ThirdStepTest extends TestCase
{
    private string $url = '/api/register/step3';

    public function test_requires_token_in_request()
    {
        $this->postJson($this->url)
            ->assertForbidden();
    }

    public function test_requires_email_in_request()
    {
        $this->postJson($this->url)->assertUnprocessable()
            ->assertJsonValidationErrorFor('email');
    }

    public function test_requires_password_in_request()
    {
        $this->postJson($this->url)->assertUnprocessable()
            ->assertJsonValidationErrorFor('password');
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
            'email' => 'test@wp.pl',
            'password' => '1aA$aaaaaaaa',
        ])
            ->assertOk()
            ->assertJsonStructure(['registration_token']);


    }


    public function test_it_accepts_email_marketing_consent_in_request()
    {
        $user = User::create([
            'phone' => Crypt::encryptString('+48123123123'),
            'ip' => '0.0.0.0'
        ]);


        $response = $this->postJson($this->url, [
            'registration_token' => $user->phone,
            'email' => 'test@wp.pl',
            'password' => '1aA$aaaaaaaa',
        ]);

        $encryptedPhone = $response->decodeResponseJson()['registration_token'];
        $user = User::firstWhere('phone', $encryptedPhone);

        if ($user) {
            $this->assertTrue($user->details->email_marketing_consent);
            $this->assertNotNull($user->details->email_marketing_consent_at);
        } else {
            $this->assertFalse($user->details->email_marketing_consent);
        }

    }


}
