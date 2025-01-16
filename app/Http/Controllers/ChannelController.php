<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Utils\Helpers;
use App\Models\Channel;
use App\Models\Message;
use App\Events\MessageSeen;
use App\Models\ChannelType;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreChannelRequest;
use App\Http\Requests\UpdateChannelRequest;

class ChannelController extends Controller
{

    /**
     * Get all public channels that user is not subscribed to.
     */
    public function getPublicChannels(): JsonResponse
    {
        $isSubscribed = false;
        return response()->json([
            'success' => true,
            'channels' => auth()->user()->getChannels(ChannelType::PUBLIC_ID, $isSubscribed)
        ]);
    }

    /**
     * Display a listing of the user's channels.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'channels' => [
                'public' => $user->getChannels(ChannelType::PUBLIC_ID),
                'private' => $user->getChannels(ChannelType::PRIVATE_ID)
            ]
        ]);
    }

    /**
     * Display the channel's info.
     */
    public function show(Channel $channel): JsonResponse
    {
        $channel->users = $channel->members();
        $channel = $channel->only([ 'id', 'name', 'icon', 'users']);

        return response()->json([
            'success' => true,
            'channel' => $channel
        ]);
    }

    /**
     * Get additional info abou the channel.
     */
    public function getInfo(Channel $channel): array
    {
        return $channel->getInfo();
    }

    /**
     * Display the channel's messages.
     */
    public function getMessages(Channel $channel, Request $request): JsonResponse
    {
        $messagesCount = $request->get('messages-count') ?? 10;
        $fromDate = $request->get('from-date') ?? null;
        $channel->messages = $channel->getMessages($messagesCount, $fromDate);
        // $channel->messages = $channel->getMessages();
        $channel->receivers = $channel->receivers();

        $channel->isPrivate = $channel->isPrivate();

        return response()->json([
            'success' => true,
            'channel' => $channel
        ]);
    }

    /**
     * Update statut of message (from unseen to seen) when user enter channel to see messages.
     */
    public function updateSeen(Channel $channel, Request $reauest): JsonResponse
    {
        $unseenMessagesIds = $channel->scopeUnseenMessages()->select('messages.id')->get()->pluck('id');
        $channel->scopeUnseenMessages()->update(['is_seen' => true]);
        $members = $channel->members();
        foreach ($members as $member) {
            MessageSeen::dispatch($member, $channel, $unseenMessagesIds->toArray());
        }

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     *  Subscribe user to channel
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function subscribe(Channel $channel): JsonResponse
    {
        $result = auth()->user()->subscribeTo($channel);

        return response()->json([
            'success' => $result,
        ]);
    }

    /**
     *  Unsubscribe user from channel
     *
     * @return Illuminate\Http\JsonResponse
     */
    public function unsubscribe(Channel $channel): JsonResponse
    {
        $result = auth()->user()->unsubscribeFrom($channel);

        return response()->json([
            'success' => $result,
        ]);
    }

}
