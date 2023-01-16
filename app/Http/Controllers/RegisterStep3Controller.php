<?php

namespace App\Http\Controllers;


use App\Http\Requests\RegisterStep3Request;

use Illuminate\Support\Facades\Hash;


class RegisterStep3Controller extends RegisterStepController
{
    protected int $step = 3;

    public function __invoke(RegisterStep3Request $request)
    {
        $this->logRequest($request->except(['password']));

        $validated = $request->validated();

        $this->updateUser($validated);

        $this->enableNextRegistrationStep();

        $this->user?->sendEmailVerificationCode();

        return $this->getReturnData();
    }


    private function updateUser(array $validated)
    {
        $this->user->update([
            'password' => Hash::make($validated['password']),
            'email' => $validated['email'],
        ]);

        if ($validated['consent'] ?? false) {
            $this->user->consentToEmailMarketing();
        }

    }


}
