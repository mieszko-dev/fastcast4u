<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterStep1Request;
use App\Http\Requests\RegisterStep2Request;
use App\Http\Requests\RegisterStep3Request;
use App\Http\Requests\RegisterStep4Request;
use App\Mail\EmailCode;
use App\Mail\PhoneCode;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     *
     * TODO create logs of all requests
     *
     * Przesyłać w requescie wszystkie dane, bez tworzenia usera?
     *
     * wykorzystać UUID?
     *
     * oddzielny registration token dla kazdego kroku
     *
     * rate limiting
     */

    public function step1(RegisterStep1Request $request)
    {
        // 1. validate phone number
        // 2. encrypt and store phone number
        // 2. generate and store OTP
        // 3. send OTP code to user
        // 4. return encrypted phone number


        $validated = $request->validated();

        $encryptedPhone = Crypt::encryptString($validated['phone']);

        $user = User::create([
            'phone' => $encryptedPhone,
            'ip' => $request->ip()
        ]);

        if ($validated['consent']) {
            $user->consentToPhoneMarketing();
        }

        $code = $user->createPhoneCode();

        Mail::to('test@wp.pl')->send(new PhoneCode($code->code));


        return [
            'registration_token' => $encryptedPhone,
        ];
    }

    public function step2(RegisterStep2Request $request)
    {
        $validated = $request->validated();

        return [
            'registration_token' => $validated['registration_token'],
        ];
    }

    public function step3(RegisterStep3Request $request)
    {
        $validated = $request->validated();

        $user = User::where('phone', $validated['registration_token'])->first();

        $user->update([
            'password' => Hash::make($validated['password']),
            'email' => $validated['email'],
        ]);

        if ($validated['consent']) {
            $user->consentToEmailMarketing();
        }

        $code = VerificationCode::createEmailCodeForUser($user);

        Mail::to($validated['email'])->send(new EmailCode($code->code));

        // send code

        // 1. validate email and encrypted phone number
        // 2. send email code to user

        return [
            'registration_token' => $validated['registration_token'],
        ];
    }

    public function step4(RegisterStep4Request $request)
    {
        $validated = $request->validated();

        $user = User::where('phone', $validated['registration_token'])->first();

        $token = auth()->login($user);


        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user_id' => $user->id,
            'email' => $user->email,
        ];


        // 1. validate code and validate email and encrypted phone number
        // 2. store email
        // 2. generate token
        // 3. login user
        // 4. return token

    }


}
