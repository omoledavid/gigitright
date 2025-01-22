<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\v1\UserResource;
use App\Traits\ApiResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use function App\Http\Controllers\Api\v1\verifyG2fa;

class AuthorizationController extends Controller {
    use ApiResponses;
    protected function checkCodeValidity($user, $addMin = 2) {
        if (!$user->ver_code_send_at) {
            return false;
        }
        if ($user->ver_code_send_at->addMinutes($addMin) < Carbon::now()) {
            return false;
        }
        return true;
    }

    public function authorization() {
        $user = auth()->user();
        if (!$user->status) {
            $type = 'ban';
        } elseif (!$user->ev) {
            $type           = 'email';
            $notifyTemplate = 'EVER_CODE';
        } elseif (!$user->sv) {
            $type           = 'sms';
            $notifyTemplate = 'SVER_CODE';
        } else {
            return $this->ok('User is already verified', new UserResource($user));
        }

        if (!$this->checkCodeValidity($user) && ($type != 'ban')) {
            $user->ver_code         = verificationCode(6);
            $user->ver_code_send_at = Carbon::now();
            $user->save();
            notify($user, $notifyTemplate, [
                'code' => $user->ver_code,
            ], [$type]);
        }

        if($type == 'ban')
        {
            return $this->ok('Account is banned', new UserResource($user));
        }

        return $this->ok('Verify your account', new UserResource($user));

    }

    public function sendVerifyCode($type) {
        $user = auth()->user();

        if ($this->checkCodeValidity($user)) {
            $targetTime = $user->ver_code_send_at->addMinutes(2)->timestamp;
            $delay      = $targetTime - time();

            $notify[] = 'Please try after ' . $delay . ' seconds';
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }

        $user->ver_code         = verificationCode(6);
        $user->ver_code_send_at = Carbon::now();
        $user->save();

        if ($type == 'email') {
            $type           = 'email';
            $notifyTemplate = 'EVER_CODE';
        } else {
            $type           = 'sms';
            $notifyTemplate = 'SVER_CODE';
        }

        notify($user, $notifyTemplate, [
            'code' => $user->ver_code,
        ], [$type]);

        $notify[] = 'Verification code sent successfully';
        return response()->json([
            'remark'  => 'code_sent',
            'status'  => 'success',
            'message' => ['success' => $notify],
        ]);
    }

    public function emailVerification(Request $request) {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();

        if ($user->ver_code == $request->code) {
            $user->ev               = 1;
            $user->email_verified_at = Carbon::now();
            $user->ver_code         = null;
            $user->save();
            $notify[] = 'Email verified successfully';
            return response()->json([
                'remark'  => 'email_verified',
                'status'  => 'success',
                'message' => ['success' => $notify],
            ]);
        }

        $notify[] = 'Verification code doesn\'t match';
        return response()->json([
            'remark'  => 'validation_error',
            'status'  => 'error',
            'message' => ['error' => $notify],
        ]);
    }

    public function mobileVerification(Request $request) {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        $user = auth()->user();
        if ($user->ver_code == $request->code) {
            $user->sv               = 1;
            $user->ver_code         = null;
            $user->ver_code_send_at = null;
            $user->save();
            $notify[] = 'Mobile verified successfully';
            return response()->json([
                'remark'  => 'mobile_verified',
                'status'  => 'success',
                'message' => ['success' => $notify],
            ]);
        }
        $notify[] = 'Verification code doesn\'t match';
        return response()->json([
            'remark'  => 'validation_error',
            'status'  => 'error',
            'message' => ['error' => $notify],
        ]);
    }

    public function g2faVerification(Request $request) {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }
        $user     = auth()->user();
        $response = verifyG2fa($user, $request->code);
        if ($response) {
            $notify[] = 'Verification successful';
            return response()->json([
                'remark'  => 'twofa_verified',
                'status'  => 'success',
                'message' => ['success' => $notify],
            ]);
        } else {
            $notify[] = 'Wrong verification code';
            return response()->json([
                'remark'  => 'validation_error',
                'status'  => 'error',
                'message' => ['error' => $notify],
            ]);
        }
    }
}
