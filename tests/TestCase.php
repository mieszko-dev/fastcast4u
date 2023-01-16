<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

//    use DatabaseMigrations;
    use RefreshDatabase;

    protected function doThreeSteps(): string
    {
        $registrationToken = $this->doTwoSteps();

        $this->postJson('/api/register/step3', [
            'registration_token' => $registrationToken,
            'email' => 'test1@example.pl',
            'password' => '1aA$aaaaaaaa',
        ]);

        return $registrationToken;
    }

    protected function doTwoSteps(): string
    {
        $registrationToken = $this->doFirstStep();

        $user = User::findByToken($registrationToken);

        $code = $user->verificationCodes()->first();

        $this->postJson('/api/register/step2', [
            'registration_token' => $registrationToken,
            'verification_code' => $code->code,
        ]);

        return $registrationToken;
    }

    protected function doFirstStep(): string
    {
        $response = $this->postJson('/api/register/step1', [
            'phone' => '+48123123123',
        ]);

        return $response->decodeResponseJson()['registration_token'];
    }

}
