<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StepsTest extends TestCase
{

    public function test_steps_cant_be_skipped()
    {
        $registrationToken = $this->doFirstStep();

        $user = User::findByToken($registrationToken);

        $this->checkStep(2, $registrationToken)->assertUnprocessable();
        $this->checkStep(3, $registrationToken)->assertForbidden();
        $this->checkStep(4, $registrationToken)->assertForbidden();

        $user->enableRegistrationStep(3);

        $this->checkStep(2, $registrationToken)->assertUnprocessable();
        $this->checkStep(3, $registrationToken)->assertUnprocessable();
        $this->checkStep(4, $registrationToken)->assertForbidden();

        $user->enableRegistrationStep(4);

        $this->checkStep(2, $registrationToken)->assertUnprocessable();
        $this->checkStep(3, $registrationToken)->assertUnprocessable();
        $this->checkStep(4, $registrationToken)->assertUnprocessable();

    }

    private function checkStep(int $step, string $registrationToken): TestResponse
    {
        return $this->postJson('/api/register/step' . $step, [
            'registration_token' => $registrationToken,
        ]);
    }


}
