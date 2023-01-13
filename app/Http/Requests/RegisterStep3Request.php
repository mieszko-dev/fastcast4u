<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterStep3Request extends FormRequest
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
            'email' => 'required|email|unique:users',
            'password' => ['required', 'max:20', Password::min(8)->mixedCase()->numbers()->symbols()],
            'consent' => 'boolean'
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
            $user = User::where('phone', $this->input('registration_token'))->first();

            if (!$user) {
                $validator->errors()->add('registration_token', 'Your registration token is invalid.');
                return;
            }

            if (!$user->hasVerifiedPhone()) {
                $validator->errors()->add('phone', 'Your phone number is not verified.');
            }

        });
    }
}
