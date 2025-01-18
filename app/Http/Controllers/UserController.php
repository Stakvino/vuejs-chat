<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Channel;
use App\Models\Message;
use App\Events\MessageSeen;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    /**
     * Get all users except auth user and blocked users.
     *
     * @return Illuminate\Http\JsonResponse
     */
    function usersListing(Request $request) : JsonResponse
    {
        $blockedUsers = auth()->user()->getBlockedUsers();
        $query = User::whereNot('id', auth()->user()->id)
        ->whereNotIn('id', $blockedUsers->pluck('id')->toArray());

        if ( $request->get('user-search') ) {
            $query->where('name', 'like', '%'. $request->get('user-search') .'%');
        }

        $users = $query->select(...User::ALLOWED_COLLUMNS)->orderBy('name')
        ->get()->map(function($user) {
                $user->avatar_path = $user->avatarPath();
                return $user;
            });

        return response()->json([
            'success' => true,
            'users' => $users,
            'blocked-users' => $blockedUsers
        ]);
    }

    /**
     * Get the authetificiated user's infos as a JSON response.
     *
     * @return Illuminate\Http\JsonResponse
     */
    function getAuthUser() : JsonResponse
    {
        $user = auth()->user();
        $user->avatar_path = $user->avatarPath();
        $user = $user->only([
            'id', 'name', 'username', 'email', 'personal_color',
            'email_verified_at', 'avatar_path'
        ]);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Display the users's info.
     */
    public function showProfile(User $user): JsonResponse
    {
        $user->avatar_path = $user->avatarPath();
        $user->is_blocked = auth()->user()->hasBlocked($user);

        $user = $user->only([
            'id', 'name', 'username', 'personal_color', 'avatar_path', 'is_blocked'
        ]);

        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    /**
     * Update authetificiated user's profile (Username and Avatar).
     *
     * @return Illuminate\Http\JsonResponse
     */
    function updateProfile(Request $request) : JsonResponse
    {
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
            $user->deleteAvatar();
        }

        $user->update($updateArray);

        return response()->json([ 'success' => true]);
    }

    /**
     * Retrive and update necessery data after a MessageSent event dispatch is received in the front-end.
     *
     * @return Illuminate\Http\JsonResponse
     */
    function messageEventReceived(Channel $channel, Message $message, Request $request) : JsonResponse
    {
        if ( $request->has('userSawMessage') ) {
            $unseenMessagesIds = $channel->scopeUnseenMessages()->select('messages.id')->get()->pluck('id');
            $channel->scopeUnseenMessages()->update(['is_seen' => $request->get('userSawMessage')]);
            $members = $channel->members();
            foreach ($members as $member) {
                MessageSeen::dispatch($member, $channel, $unseenMessagesIds->toArray());
            }
        }

        return response()->json([
            'success' => true,
            'channelInfo' => $channel->getInfo(),
            'messageInfo' => $message->getInfo()
        ]);
    }

    /**
     * Block a user.
     */
    public function block(User $user): JsonResponse
    {
        auth()->user()->block($user);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Unblock a user.
     */
    public function unblock(User $user): JsonResponse
    {
        auth()->user()->unblock($user);

        return response()->json([
            'success' => true,
        ]);
    }

}
