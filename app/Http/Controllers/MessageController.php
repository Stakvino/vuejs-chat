<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Channel;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\ChannelType;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
     *
     *  @return Illuminate\Http\JsonResponse
     */
    public function store(StoreMessageRequest $request) : JsonResponse
    {
        $sender = auth()->user();
        if ( $request->get('channel_id') ) {
            $channel = Channel::find($request->get('channel_id'));
        }
        else {
            // Create the new channel if it doesnt exist
            $receivers_ids = $request->get('receivers_ids');
            $channelIsPrivate = $receivers_ids && sizeof($receivers_ids) === 1;
            $channel = Channel::create([
                'channel_type_id' => $channelIsPrivate ? ChannelType::PRIVATE_ID : ChannelType::PUBLIC_ID
            ]);
            // Subscribe the users to the channel
            $sender->subscribeTo($channel);
            if ( $receivers_ids ) {
                foreach ($receivers_ids as $receiver_id) {
                    User::find($receiver_id)->subscribeTo($channel);
                }
            }
        }

        $sender->sendMessage($channel, $request->get('text'));

        MessageSent::dispatch($channel);

        return response()->json(['success' => true]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMessageRequest $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        //
    }
}
