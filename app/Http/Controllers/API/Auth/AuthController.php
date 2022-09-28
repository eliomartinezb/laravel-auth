<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\API\BaseController;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    /**
     * Register api
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:8'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', (array)$validator->errors(), 500);
        }

        $success = DB::transaction(function() use ($request) {
            $input = $request->all();
            $input['password'] = bcrypt($input['password']);
            $user = User::create($input);
            $success['token'] = $user->createToken('MyApp')->accessToken;
            $success['name'] = $user->name;

            event(new Registered($user));

            return $success;
        });

        return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Login api
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        Log::info("login started");

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }

        $user = Auth::user();
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['name'] = $user->name;

        return $this->sendResponse($success, 'User login successfully.');
    }

    //START FORGOT PASSWORD
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $input = $request->only('email');
        $validator = Validator::make($input, [
            'email' => "required|email"
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error', (array)$validator->errors(), 400);
        }

        $response = Password::sendResetLink($input);

        if ($response !== Password::RESET_LINK_SENT) {
            return $this->sendError('Oops', [], 400);
        }

        $message = 'Mail send successfully';

        return $this->sendResponse([], $message);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function checkResetPasswordToken($token, Request $request): JsonResponse
    {
        $input = [
            'token' => $token
        ];
        $validator = Validator::make($input, [
            'token' => "required"
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error', (array)$validator->errors(), 404);
        }
        $results = DB::table('password_resets')->where($input);

        if ($results->count() == 0) {
            return $this->sendError('DB error', []);
        }

        $message = "Éxito";

        return $this->sendResponse([], $message);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function passwordReset(Request $request): JsonResponse
    {
        Log::info('passwordReset started');
        $input = $request->only('email', 'token', 'password', 'password_confirmation');
        $validator = Validator::make($input, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error', (array)$validator->errors(), 404);
        }
        /*
        Log::info('passwordReset reset started');
        $tokenData = DB::table('password_resets')->where('token', Hash::make($request->token))->first();
        dd($tokenData);
        $user = User::where('email', $tokenData->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();
        $user->tokens()->delete();
        event(new PasswordReset($user));
        Log::info('passwordReset reset ended');
        */

        $status = Password::reset($input, function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            return $this->sendError(trans($status), [], 500);
        }

        Log::info('passwordReset ended');
        return $this->sendResponse([], 'Password reset successfully');
    }
}
