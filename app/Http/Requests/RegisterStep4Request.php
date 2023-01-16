<?php

namespace App\Http\Requests;

use App\Models\RegistrationStep;
use App\Models\VerificationCode;
use Illuminate\Foundation\Http\FormRequest;

class RegisterStep4Request extends RegisterStepRequest
{
    protected ?int $stepNumber = 4;
    protected ?string $shouldValidateCodeType = VerificationCode::EMAIL;

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
