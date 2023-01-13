<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Foundation\Http\FormRequest;

class RegisterStep2Request extends FormRequest
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

//    protected function prepareForValidation()
//    {
//        $this->merge([
//            'slug' => Str::slug($this->slug),
//        ]);
//    }

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
                    'type' => VerificationCode::PHONE
                ])
                ->first();

            if (!$code) {
                return;
            }


            if ($code->user->hasVerifiedPhone()) {
                $validator->errors()->add('verification_code', 'Phone number already verified.');
                return;
            }


            if ($code->user->phone !== $this->input('registration_token')) {
                $validator->errors()->add('registration_token', 'The registration token is invalid.');
                return;
            }

            $code->user->markPhoneAsVerified();
            $code->delete();

        });
    }
}
