<?php

namespace App\Http\Requests;


use Illuminate\Validation\Rules\Password;

class RegisterStep3Request extends RegisterStepRequest
{
    protected ?int $stepNumber = 3;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            ...$this->stepRules(),
            'email' => 'required|email|unique:users',
            'password' => ['required', 'max:20', Password::min(8)->mixedCase()->numbers()->symbols()],
            'consent' => 'sometimes|boolean'
        ];
    }

}
