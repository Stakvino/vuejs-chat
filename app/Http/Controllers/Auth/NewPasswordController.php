<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     *
     * @return Illuminate\Http\JsonResponse;
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ( $validator->fails() ) {
            return response()->json([
                'validation_error' => true,
                'error_messages' => $validator->errors()
           ]);
        }

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            return response()->json([
                'validation_error' => true,
                'error_messages' => [
                    "email" => [__($status)]
                ]
           ]);
            /*throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);*/
        }

        return response()->json([
            'success' => true,
            'success_message' => __($status),
        ]);
    }

    /**
     * Handle password update request.
     *
     * @return Illuminate\Http\JsonResponse;
     */
    public function update(Request $request): JsonResponse
    {

        $user = auth()->user();
        if ( !Hash::check($request->get('current_password'),  $user->password) ) {
            return response()->json([
                'validation_error' => true,
                'error_messages' => ['current_password' => ['The password is not correct']]
           ]);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ( $validator->fails() ) {
            return response()->json([
                'validation_error' => true,
                'error_messages' => $validator->errors()
           ]);
        }

        $user->update([
            'password' => Hash::make($request->string('password')),
        ]);

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'success_message' => 'Password changed',
        ]);
    }

}
