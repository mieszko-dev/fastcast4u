<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;

class RegisterStep1Request extends FormRequest
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
            'phone' => 'required|string|phone:AUTO', // check if not used or used and not verified
            'consent' => 'boolean'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
//    public function withValidator($validator)
//    {
//        $validator->after(function ($validator) {
//
//            $encryptedPhone = Crypt::encryptString($this->input('phone'));
//            if (User::where('phone', $encryptedPhone)->exists()) {
//                $validator->errors()->add('phone', 'Your phone number is not verified.');
//            }
//
//        });
//    }
}
