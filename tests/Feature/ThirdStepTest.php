<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\EmailCode;
use App\Notifications\PhoneCode;
use App\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
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
        $registrationToken = $this->doTwoSteps();

        $this->postJson($this->url, ['registration_token' => $registrationToken])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('email');
    }

    public function test_requires_unique_email_in_request()
    {
        $registrationToken = $this->doTwoSteps();

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'email' => 'test2@wp.pl',
            'password' => '1aA$aaaaaaaa',
        ]);

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'email' => 'test2@wp.pl',
            'password' => '1aA$aaaaaaaa',
        ])->assertJsonValidationErrorFor('email');

    }

    public function test_requires_password_in_request()
    {
        $registrationToken = $this->doTwoSteps();

        $this->postJson($this->url, ['registration_token' => $registrationToken])
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('password');
    }


    public function test_it_accepts_email_marketing_consent_in_request()
    {

        $registrationToken = $this->doTwoSteps();

        $user = User::findByToken($registrationToken);

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'email' => 'test1@wp.pl',
            'password' => '1aA$aaaaaaaa',
            'consent' => true
        ])
            ->assertOk()
            ->assertJson(['registration_token' => $registrationToken]);

        $this->assertTrue($user->details->email_marketing_consent);
        $this->assertNotNull($user->details->email_marketing_consent_at);


    }

    public function test_it_returns_token()
    {
        $registrationToken = $this->doTwoSteps();

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'email' => 'test1@wp.pl',
            'password' => '1aA$aaaaaaaa',
        ])
            ->assertOk()
            ->assertJson(['registration_token' => $registrationToken]);

    }

    public function test_it_generates_verification_code()
    {

        $registrationToken = $this->doTwoSteps();

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'email' => 'test1@wp.pl',
            'password' => '1aA$aaaaaaaa',
        ]);

        $user = User::findByToken($registrationToken);

        $this->assertDatabaseHas('verification_codes', ['user_id' => $user->id, 'type' => VerificationCode::EMAIL]);

    }

    public function test_it_sends_verification_code_to_phone()
    {
        $registrationToken = $this->doTwoSteps();
        Notification::fake();

        $this->postJson($this->url, [
            'registration_token' => $registrationToken,
            'email' => 'test1@wp.pl',
            'password' => '1aA$aaaaaaaa',
        ]);

        $user = User::findByToken($registrationToken);
//
        Notification::assertSentTo($user, EmailCode::class);

    }


}
