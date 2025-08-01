<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UserResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ForgotPasswordController extends Controller
{
    use ApiResponses;

    public function sendResetCodeEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error.');
        }

        $fieldType = $this->findFieldType();
        $user = User::where($fieldType, $request->value)->first();

        if (!$user) {
            return $this->error('Couldn\'t find any account with this information');
        }

        PasswordReset::where('email', $user->email)->delete();
        $code = verificationCode(6);
        $password = new PasswordReset();
        $password->email = $user->email;
        $password->token = $code;
        $password->created_at = \Carbon\Carbon::now();
        $password->save();

        $userIpInfo = getIpInfo();
        $userBrowserInfo = osBrowser();
        notify($user, 'PASS_RESET_CODE', [
            'code' => $code,
            'operating_system' => @$userBrowserInfo['os_platform'],
            'browser' => @$userBrowserInfo['browser'],
            'ip' => @$userIpInfo['ip'],
            'time' => @$userIpInfo['time'],
        ]);

        $email = $user->email;
        return $this->ok('Verification code sent to mail', [
            'email' => $email,
        ]);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error.', err: $validator->errors()->all());
        }
        $code = $request->code;

        if (PasswordReset::where('token', $code)->where('email', $request->email)->count() != 1) {
            return $this->error('Verification code doesn\'t match');
        }

        return $this->ok('You can change your password.');
    }

    public function reset(Request $request)
    {

        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return $this->error('Validation Error.', err: $validator->errors()->all());
        }
        $reset = PasswordReset::where('email', $request->email)->orderBy('created_at', 'desc')->first();
//        if (!$reset) {
//            return $this->error('Invalid verification code');
//        }

        $user = User::where('email', $reset->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        $userIpInfo = getIpInfo();
        $userBrowser = osBrowser();
        notify($user, 'PASS_RESET_DONE', [
            'operating_system' => @$userBrowser['os_platform'],
            'browser' => @$userBrowser['browser'],
            'ip' => @$userIpInfo['ip'],
            'time' => @$userIpInfo['time'],
        ]);
        $reset->delete();

        return $this->ok('Password changed successfully', [
            'user' => new UserResource($user),
        ]);
    }

    protected function rules()
    {
        $passwordValidation = Password::min(6);
//        $general            = GeneralSetting::first();
//        if ($general->secure_password) {
//            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
//        }
        return [
//            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', $passwordValidation],
        ];
    }

    private function findFieldType()
    {
        $input = request()->input('value');

        $fieldType = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$fieldType => $input]);
        return $fieldType;
    }
}
