<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterStep1Request;
use App\Http\Requests\RegisterStep2Request;
use App\Http\Requests\RegisterStep3Request;
use App\Http\Requests\RegisterStep4Request;
use App\Mail\EmailCode;
use App\Mail\PhoneCode;
use App\Models\RegistrationStep;
use App\Models\User;
use App\Models\VerificationCode;
use App\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterStepController extends Controller
{

    protected int $step;
    protected ?User $user;

    public function __construct()
    {
        $this->user = $this->getCurrentUser();
    }

    protected function getCurrentUser(): ?User
    {
        return request()->input('registration_token') ?
            User::findByToken(request()->input('registration_token'))
            : null;
    }

    protected function logRequest(array $data)
    {
        Log::info('Step ' . $this->step . ' request', ['data' => $data, 'uri' => request()->url(), 'method' => request()->method()]);
    }

    protected function enableNextRegistrationStep()
    {
        $this->user?->enableRegistrationStep($this->step + 1);
    }

    protected function getReturnData(): array
    {
        $response = ['registration_token' => $this->user?->getRegistrationToken()];

        $this->logResponse($response);

        return $response;
    }

    protected function logResponse(array $data)
    {
        Log::info('Step ' . $this->step . ' response', ['data' => $data]);
    }


}
