<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
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
        $registrationToken = $this->doFirstStep();

        $this->postJson($this->url, ['registration_token' => $registrationToken])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('verification_code');
    }

    public function test_it_validates_verification_code()
    {
        $registrationToken = $this->doFirstStep();

        $user = User::findByToken($registrationToken);
        $code = $user->verificationCodes()->first();

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'verification_code' => $code->code,
        ])->assertOk();

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'verification_code' => '000000',
        ])->assertJsonValidationErrorFor('verification_code');

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'verification_code' => 123,
        ])->assertJsonValidationErrorFor('verification_code');

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'verification_code' => '23fe13',
        ])->assertJsonValidationErrorFor('verification_code');
    }

    public function test_it_returns_token()
    {
        $registrationToken = $this->doFirstStep();

        $user = User::findByToken($registrationToken);
        $code = $user->verificationCodes()->first();

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'verification_code' => $code->code,
        ])->assertJson(['registration_token' => $registrationToken]);

    }


}
