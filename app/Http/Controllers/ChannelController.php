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

        return response()->json([
            'success' => true,
            'channels' => [
                'public' => $user->publicChannels(),
                'private' => $user->privateChannels()
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
        $channel->messages = $channel->getMessages(10);
        $channel->receivers = $channel->receivers()->map(function($receiver) {
            $receiver->avatar_path = $receiver->avatarPath();
            return $receiver;
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
