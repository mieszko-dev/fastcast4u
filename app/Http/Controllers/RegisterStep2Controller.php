<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterStep2Request;
use App\Models\VerificationCode;

class RegisterStep2Controller extends RegisterStepController
{
    protected int $step = 2;


    public function __invoke(RegisterStep2Request $request)
    {
        $this->logRequest($request->all());

        $this->enableNextRegistrationStep();

        return $this->getReturnData();
    }


}
