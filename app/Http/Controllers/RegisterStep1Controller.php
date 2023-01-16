<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterStep1Request;
use App\Models\RegistrationStep;
use App\Models\User;
use App\Phone;


class RegisterStep1Controller extends RegisterStepController
{
    protected int $step = 1;

    public function __invoke(RegisterStep1Request $request)
    {
        $this->logRequest($request->except(['phone']));

        $validated = $request->validated();

        $this->user = $this->createUser($validated);

        if ($validated['consent'] ?? false) {
            $this->user->consentToPhoneMarketing();
        }

        RegistrationStep::createSteps($this->user);

        $this->enableNextRegistrationStep();

        $this->user->sendPhoneVerificationCode();

        return $this->getReturnData();
    }

    private function createUser(array $validated)
    {
        return User::create([
            'encrypted_phone' => Phone::encrypt($validated['phone']),
            'phone_hash' => Phone::hash($validated['phone']),
            'ip' => request()->ip(),
        ]);
    }


}
