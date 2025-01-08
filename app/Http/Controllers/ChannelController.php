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
     * Get additional info abou the channel.
     */
    public function getInfo(Channel $channel): array
    {
        return $channel->getInfo();
    }

    /**
     * Display the channel's messages.
     */
    public function getMessages(Channel $channel): JsonResponse
    {
        $channel->messages = $channel->getMessages(10);
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
        MessageSeen::dispatch($channel, $unseenMessagesIds->toArray());

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
