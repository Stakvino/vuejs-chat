<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Get the authetificiated user's infos as a JSON response.
     *
     * @return Illuminate\Http\JsonResponse
     */
    function getAuthUser() : JsonResponse
    {
        if ( auth()->check() ) {
            $user = auth()->user();
            $response = $user->first(['name', 'username', 'email', 'personal_color'])->toArray();
            $response['avatar_path'] = $user->avatarPath();
            return response()->json([
                'success' => true,
                'user' => $response
            ]);
        }

        return response()->json([
            'error' => true,
            'error_message' => 'User is not signed up'
        ]);
    }

    /**
     * Update authetificiated user's profile (Username and Avatar).
     *
     * @return Illuminate\Http\JsonResponse
     */
    function updateProfile(Request $request) : JsonResponse
    {
        if ( auth()->check() ) {
            $user = auth()->user();
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'min:3', 'max:30'],
            ]);
            if ( $validator->fails() ) {
                return response()->json([
                    'validation_error' => true,
                    'error_messages' => $validator->errors()
               ]);
            }

            $updateArray = ['name' => $request->name];

            // Store new user avatar if he did upload a file
            if ( $request->hasFile('avatar') ) {
                $user->deleteAvatar();
                $avatar = $user->id . "-" . now()->format('d_m_y_h_i_s');
                $request->file('avatar')->storeAs('/avatars', $avatar, 'images' );
                $updateArray['avatar'] = $avatar;
            }
            // Also delete avatar if user removed hes image
            if ($request->get("avatar") == "null") {
                $user->deleteAvatar();;
            }

            $user->update($updateArray);

            return response()->json([ 'success' => true]);
        }

        return response()->json([
            'error' => true,
            'error_message' => 'User is not signed up'
        ]);
    }

}
