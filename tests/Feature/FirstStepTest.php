<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\PhoneCode;
use App\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class FirstStepTest extends TestCase
{

    private string $phone = '123123123';
    private string $url = '/api/register/step1';

    public function test_requires_phone_number_in_request()
    {
        $response = $this->postJson($this->url);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrorFor('phone');
    }

    public function test_requires_valid_phone_number_in_request()
    {
        $this->postJson($this->url, [
            'phone' => $this->phone,
        ])->assertUnprocessable()
            ->assertJsonValidationErrorFor('phone');

        $this->postJson($this->url, [
            'phone' => '0048' . $this->phone,
        ])->assertUnprocessable()
            ->assertJsonValidationErrorFor('phone');

        $this->postJson($this->url, [
            'phone' => '48' . $this->phone,
        ])->assertUnprocessable()
            ->assertJsonValidationErrorFor('phone');

        $this->postJson($this->url, [
            'phone' => '+48' . $this->phone,
        ])->assertOk();
    }

    public function test_it_checks_if_number_is_unique()
    {
        $phone = '+48' . $this->phone;
        $this->postJson($this->url, [
            'phone' => $phone,
        ])->assertOk();

        $this->postJson($this->url, [
            'phone' => $phone,
        ])->assertJsonValidationErrorFor('phone');

    }

    public function test_it_creates_user_when_phone_is_valid()
    {
        $phone = '+48' . $this->phone;
        $response = $this->postJson($this->url, [
            'phone' => $phone,
        ])->assertOk();


        $this->assertDatabaseHas('users', ['phone_hash' => Phone::hash($phone)]);

    }

    public function test_it_saves_encrypted_and_hashed_phone_when_valid_phone_is_provided()
    {
        $phone = '+48' . $this->phone;
        $response = $this->postJson($this->url, [
            'phone' => $phone,
        ])->assertOk();

        $registrationToken = $response->decodeResponseJson()['registration_token'];
        $user = User::findByToken($registrationToken);

        $this->assertNotEquals($user->encrypted_phone, $phone);
        $this->assertNotEquals($user->phone_hash, $phone);

    }

    public function test_it_accepts_phone_marketing_consent_in_request()
    {
        $response = $this->postJson($this->url, [
            'phone' => '+48' . $this->phone,
            'consent' => true
        ]);

        $registrationToken = $response->decodeResponseJson()['registration_token'];
        $user = User::findByToken($registrationToken);

        $this->assertTrue($user->details->phone_marketing_consent);
        $this->assertNotNull($user->details->phone_marketing_consent_at);

    }


    // Token is encrypted phone
    public function test_it_returns_token()
    {
        $phone = '+48' . $this->phone;

        $response = $this->postJson($this->url, [
            'phone' => $phone,
        ])->assertJsonStructure(['registration_token']);

        $registrationToken = $response->decodeResponseJson()['registration_token'];

        $this->assertEquals(
            $phone,
            Crypt::decryptString($registrationToken)
        );
    }

    public function test_it_generates_verification_code()
    {

        $phone = '+48' . $this->phone;
        $response = $this->postJson($this->url, [
            'phone' => $phone,
        ])->assertOk();

        $registrationToken = $response->decodeResponseJson()['registration_token'];
        $user = User::findByToken($registrationToken);

        $this->assertDatabaseHas('verification_codes', ['user_id' => $user->id, 'type' => VerificationCode::PHONE]);

    }

    public function test_it_sends_verification_code_to_phone()
    {
        Notification::fake();

        $phone = '+48' . $this->phone;
        $response = $this->postJson($this->url, [
            'phone' => $phone,
        ])->assertOk();

        $registrationToken = $response->decodeResponseJson()['registration_token'];
        $user = User::findByToken($registrationToken);

        Notification::assertSentTo($user, PhoneCode::class);

    }


}
