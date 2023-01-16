<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class SecondStepTest extends TestCase
{
    private string $url = '/api/register/step2';

    public function test_requires_token_in_request()
    {
        $this->postJson($this->url)
            ->assertForbidden();
    }

    public function test_requires_verification_code_in_request()
    {
        $response = $this->postJson('/api/register/step1', [
            'phone' => '+48123123123',
        ])->assertOk();

        $registrationToken = $response->decodeResponseJson()['registration_token'];

        $this->postJson($this->url, ['registration_token' => $registrationToken])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('verification_code');
    }

    public function test_it_validates_verification_code()
    {
        $response = $this->postJson('/api/register/step1', [
            'phone' => '+48123123123',
        ])->assertOk();
        $registrationToken = $response->decodeResponseJson()['registration_token'];
        $user = User::findByToken($registrationToken);
        $code = $user->verificationCodes()->first();

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'verification_code' => '000000',
        ])->assertJsonValidationErrorFor('verification_code');

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'verification_code' => 123,
        ])->assertJsonValidationErrorFor('verification_code');
    }


}
