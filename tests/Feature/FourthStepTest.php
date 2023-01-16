<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class FourthStepTest extends TestCase
{

    private string $url = '/api/register/step4';

    public function test_requires_token_in_request()
    {
        $this->postJson($this->url)
            ->assertForbidden();
    }

    public function test_requires_verification_code_in_request()
    {
        $registrationToken = $this->doThreeSteps();


        $this->postJson($this->url, ['registration_token' => $registrationToken])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('verification_code');
    }

    public function test_it_validates_verification_code()
    {
        $registrationToken = $this->doThreeSteps();


        $user = User::findByToken($registrationToken);
        $code = $user->verificationCodes()->firstWhere('type', VerificationCode::EMAIL);

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'verification_code' => $code->code,
        ])->assertOk();

    }

    public function test_it_validates_verification_code_type()
    {
        $registrationToken = $this->doThreeSteps();

        $user = User::findByToken($registrationToken);
        $validCode = $user->verificationCodes()->firstWhere('type', VerificationCode::EMAIL);
        $invalidCode = $user->verificationCodes()->firstWhere('type', VerificationCode::PHONE);

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'verification_code' => $invalidCode->code,
        ])->assertUnprocessable();

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'verification_code' => $validCode->code,
        ])->assertOk();

    }


    public function test_it_returns_jwt_token()
    {
        $registrationToken = $this->doThreeSteps();

        $user = User::findByToken($registrationToken);
        $validCode = $user->verificationCodes()->firstWhere('type', VerificationCode::EMAIL);

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'verification_code' => $validCode->code,
        ])->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in'
        ]);

    }


}
