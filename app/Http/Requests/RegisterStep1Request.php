<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Phone;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class RegisterStep1Request extends RegisterStepRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'phone' => [
                'required', 'string', 'phone:AUTO',
                function ($attribute, $value, $fail) {
                    if (User::where('phone_hash', Phone::hash($value))->exists()) {
                        $fail('The ' . $attribute . ' is already used.');
                    }
                },
            ],
            'consent' => 'sometimes|boolean'
        ];
    }
}
