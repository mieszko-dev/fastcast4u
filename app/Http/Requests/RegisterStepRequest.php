<?php

namespace App\Http\Requests;

use App\Models\RegistrationStep;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RegisterStepRequest extends FormRequest
{

    protected ?int $stepNumber = null;
    protected ?string $shouldValidateCodeType = null;

    private ?RegistrationStep $step = null;
    private ?User $user;

    public function authorize()
    {
        if (!$this->stepNumber) return true;

        $this->user = $this->input('registration_token') ?
            User::findByToken($this->input('registration_token'))
            : null;

        if (!$this->user) return false;

        return RegistrationStep::where([
                'user_id' => $this->user->id,
                'step' => $this->stepNumber,
                'enabled' => true
            ]
        )->exists();
    }


    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->shouldValidateCodeType) {
                $this->validateCode($validator);
            }
        });

    }

    private function validateCode(Validator $validator)
    {
        $verificationCode = VerificationCode::query()
            ->firstWhere([
                'code' => $this->input('verification_code'),
                'user_id' => $this->user->id,
                'type' => $this->shouldValidateCodeType
            ]);

        if (!$verificationCode) {
            $validator->errors()->add('verification_code', 'Verification code is invalid');
        }

    }

    protected function stepRules(): array
    {
        $rules = [
            'registration_token' => 'required|exists:users,encrypted_phone',
        ];

        if ($this->shouldValidateCodeType) {
            $rules['verification_code'] = 'required|integer|digits:6';
        }

        return $rules;

    }

}
