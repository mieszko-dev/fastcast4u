<?php

namespace App\Http\Requests;

use App\Models\VerificationCode;
use Illuminate\Foundation\Http\FormRequest;

class RegisterStep4Request extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'registration_token' => 'required|exists:users,phone',
            'verification_code' => 'required|integer|digits:6|exists:verification_codes,code'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {


            $code = VerificationCode::with('user')
                ->where([
                    'code' => $this->input('verification_code'),
                    'type' => VerificationCode::EMAIL
                ])
                ->first();


            if (!$code) {
                return;
            }

            if ($code->user->hasVerifiedPhone()) {
                $validator->errors()->add('verification_code', 'Phone already verified.');
                return;
            }


            if ($code->user->hasVerifiedEmail()) {
                $validator->errors()->add('verification_code', 'Email already verified.');
                return;
            }


            if ($code->user->phone !== $this->input('registration_token')) {
                $validator->errors()->add('registration_token', 'The registration token is invalid.');
                return;
            }

            $code->user->markEmailAsVerified();
            $code->delete();

        });
    }
}
