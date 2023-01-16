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
        $this->postJson($this->url)->assertUnprocessable()
            ->assertJsonValidationErrorFor('verification_code');
    }


}
