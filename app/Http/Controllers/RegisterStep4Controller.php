<?php

namespace App\Http\Controllers;


use App\Http\Requests\RegisterStep4Request;


class RegisterStep4Controller extends RegisterStepController
{
    protected int $step = 4;

    public function __invoke(RegisterStep4Request $request)
    {
        $this->logRequest($request->except(['password']));

        $token = auth()->login($this->user);

        $response = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ];

        $this->user->verificationCodes()->delete();
        $this->user->registrationSteps()->delete();

        $this->logResponse($response);

        return $response;

    }

}
