<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Crypt;
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

    public function test_it_accepts_phone_marketing_consent_in_request()
    {
        $response = $this->postJson($this->url, [
            'phone' => '+48' . $this->phone,
            'consent' => true
        ]);

        $encryptedPhone = $response->decodeResponseJson()['registration_token'];
        $user = User::firstWhere('phone', $encryptedPhone);

        if ($user) {
            $this->assertTrue($user->details->phone_marketing_consent);
            $this->assertNotNull($user->details->phone_marketing_consent_at);
        } else {
            $this->assertFalse($user->details->phone_marketing_consent);
        }

    }


    // Token is encrypted phone
    public function test_it_returns_token_number()
    {
        $phone = '+48' . $this->phone;

        $response = $this->postJson($this->url, [
            'phone' => $phone,
        ])->assertJsonStructure(['registration_token']);

        $encryptedPhone = $response->decodeResponseJson()['registration_token'];

        $this->assertEquals(
            $phone,
            Crypt::decryptString($encryptedPhone)
        );
    }


}
