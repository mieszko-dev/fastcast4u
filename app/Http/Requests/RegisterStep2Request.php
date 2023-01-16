<?php

namespace App\Http\Requests;


use App\Models\VerificationCode;

class RegisterStep2Request extends RegisterStepRequest
{
    protected ?int $stepNumber = 2;
    protected ?string $shouldValidateCodeType = VerificationCode::PHONE;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return $this->stepRules();
    }


}
