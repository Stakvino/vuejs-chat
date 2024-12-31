<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Utils\Helpers;
use App\Models\Channel;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreChannelRequest;
use App\Http\Requests\UpdateChannelRequest;

class ChannelController extends Controller
{

    /**
     * Display a listing of the user's channels.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $channels = $user->privateChannels();
        foreach ($channels as $key => $channel) {
            $channel->sender = $channel->senders()->first();
            $channel->unseenMessagesCount = $channel->scopeUnseenMessages()->count();
            $channel->lastMessage = $channel->lastMessage();
        }

        // Formating dates and adding users avatar path
        $channels = $channels->map(function($channel) {
            $channel->sender->avatar_path = $channel->sender->avatarPath();
            $channel->lastMessage->since = Helpers::dateTimeFormat($channel->lastMessage->created_at);
            return $channel;
        });

        $channels = $channels->sortBy(function ($channel) {
            return $channel->lastMessage->created_at;
        });

        // Fix index changes because google chrome will change then back
        $channels = $channels->values();

        return response()->json([
            'success' => true,
            'channels' => [
                'public' => [Channel::publicChannel()],
                'private' => $channels
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChannelRequest $request)
    {
        //
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
     * Display the channel's messages.
     */
    public function getMessages(Channel $channel): JsonResponse
    {
        $channel->messages = $channel->latestMessages(10)
        ->map(function($message) {
            $message->format_created_at = $message->created_at->format('h:i');
            $message->isMyMessage = $message->isMyMessage();
            $message->usersSeen = $message->usersSeen();
            $message->isSeenByAuth = $message->isSeenBy(auth()->user());
            $message->sender = $message->sender();
            $message->sender->avatar_path = $message->sender->avatarPath();
            return $message;
        });
        $channel->senders = $channel->senders()->map(function($sender) {
            $sender->avatar_path = $sender->avatarPath();
            return $sender;
        });

        $channel->isPrivate = $channel->isPrivate();

        return response()->json([
            'success' => true,
            'channel' => $channel
        ]);
    }

    /**
     * Update statut of message (from unseen to seen) when user enter channel to see messages.
     */
    public function updateSeen(Channel $channel): JsonResponse
    {
        $channel->scopeUnseenMessages()->update(['is_seen' => true]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Channel $channel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChannelRequest $request, Channel $channel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Channel $channel)
    {
        //
    }

}
