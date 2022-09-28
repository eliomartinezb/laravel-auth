<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\BaseController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends BaseController
{
    /**
     * @param $user_id
     * @param Request $request
     * @return JsonResponse
     */
    public function verify($user_id, Request $request): JsonResponse
    {
        if (!$request->hasValidSignature()) {
            return response()->json(["msg" => "Invalid/Expired url provided."], 401);
        }

        $user = User::findOrFail($user_id);

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }
        //TODO: Deberia de redirigir a otra pantalla
        return $this->sendResponse([], "Email has been verified successfully");
    }

    /**
     * @return JsonResponse
     */
    public function resend(): JsonResponse
    {
        if (auth()->user()->hasVerifiedEmail()) {
            return response()->json(["msg" => "Email already verified."], 400);
        }

        auth()->user()->sendEmailVerificationNotification();

        return $this->sendResponse([], "Email verification link sent on your email id");
    }
}
