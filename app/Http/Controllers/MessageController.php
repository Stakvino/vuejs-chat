<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Channel;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\ChannelType;
use Illuminate\Http\Request;
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
            // Create the new private channel if it doesnt exist
            $receiver = User::find($request->get('receiver_id'));
            $channelAlreadyExist = $sender->inChannelWith($receiver);
            if ( $channelAlreadyExist ) {
                $channel = $channelAlreadyExist;
            }
            else {
                $channel = Channel::create(['channel_type_id' => ChannelType::PRIVATE_ID]);
                // Subscribe the users to the channel
                $sender->subscribeTo($channel);
                $receiver->subscribeTo($channel);
            }
        }

        $message = $sender->sendMessage($channel, $request->get('text'));

        MessageSent::dispatch($channel, $message);

        $channel->info = $channel->getInfo();
        $message->info = $message->getInfo();

        return response()->json([
            'success' => true,
            'channel' => $channel,
            'message' => $message
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
        //
    }

    /**
     * Get messages collection using Ids from the $request.
     */
    public function getMessages(Request $request): array
    {
        if ( !$request->get('messages-ids') ) return [];

        $messages = Message::whereIn('id', $request->get('messages-ids'))->get()
        ->map(function ($message) {
            $message->info = $message->getInfo();
            return $message;
        })->toArray();

        return $messages;
    }

    /**
     * Get additional info abou the channel.
     */
    public function getInfo(Message $message): array
    {
        return $message->getInfo();
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
