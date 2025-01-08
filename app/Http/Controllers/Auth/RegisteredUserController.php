<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Channel;
use App\Models\ChannelUser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:3', 'max:30'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ( $validator->fails() ) {
            return response()->json([
                'validation_error' => true,
                'error_messages' => $validator->errors()
           ]);
        }

        // Make a hex custom color that will be used in the default avatar image
        $personalColor = \bin2hex($request->name);
        if ( \strlen($personalColor) < 6 ) {
            $missingCharachters = 6 - \strlen($personalColor);
            $personalColor .= str_repeat('A', $missingCharachters);
        }
        $personalColor = \substr($personalColor, 0, 6);
        $personalColor = '#' . $personalColor;
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
            'personal_color' => $personalColor,
            'last_login_at' => now()
        ]);

        // Subscribe the user automaticly to the public channel
        ChannelUser::create([
            'user_id' => $user->id,
            'channel_id' => Channel::PUBLIC_CHANNEL_ID
        ]);

        // Username format example : stak#0005
        $formatedName = \strtolower( \str_replace(' ', '', $user->name) );
        $user->update([
            'username' => $formatedName . '#' . \str_pad($user->id, 4, 0, STR_PAD_LEFT)
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->json([
            'success' => true,
            'redirect' => '/email/verify'
       ]);
    }
}
