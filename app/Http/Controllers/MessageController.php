<?php

namespace App\Http\Controllers;

use App\Models\User;
use GeminiAPI\Client;
use App\Models\Channel;
use App\Models\Message;
use App\Events\MessageSent;
use App\Events\UserWriting;
use App\Models\ChannelType;
use App\Models\MessageType;
use Illuminate\Http\Request;
use App\Events\ChannelCreated;
use App\Events\MessageDeleted;
use Illuminate\Http\JsonResponse;
use GeminiAPI\Resources\ModelName;
use Illuminate\Support\Facades\Http;
use GeminiAPI\Resources\Parts\TextPart;
use App\Http\Requests\StoreMessageRequest;
use App\Http\Requests\UpdateMessageRequest;

class MessageController extends Controller
{

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

        $message = [
            'text' => $request->get('text'),
            'type' => MessageType::find(MessageType::TEXT_ID)
        ];
        if ( $request->hasFile('attachment') ) {
            $message['type'] = MessageType::find(MessageType::FILE_ID);
            $message['attachment'] = $request->file('attachment');
        }

        if ( $request->hasFile('audio') ) {
            $message['type'] = MessageType::find(MessageType::AUDIO_ID);
            $message['audio'] = $request->file('audio');
            $message['audio-duration'] = $request->get('audio-duration');
        }

        $message = $sender->sendMessage($channel, $message);

        if ( $message === null ) {
            return response()->json( ['success' => false], 403);
        }

        $members = $channel->members();
        foreach ($members as $member) {
            MessageSent::dispatch($member, $channel, $message);
        }

        $channel->info = $channel->getInfo();
        $message->info = $message->getInfo();

        return response()->json([
            'success' => true,
            'channel' => $channel,
            'message' => $message
        ]);
    }

    /**
     * Store message sent by chat bot.
     *
     *  @return Illuminate\Http\JsonResponse
     */
    public function robotMessage(Request $request) : JsonResponse
    {

        $geminiApiKey = "AIzaSyD8TwWBgkQIv3aHEF4PI78ULSzG1wBfsBo";
        $geminiResponse = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$geminiApiKey",
        [
            "contents" => [
                (object) [
                    "parts" =>
                    [
                        (object) [ "text" => $request->get('text') ]
                    ]
                ]
            ]
        ]);

        if ( $geminiResponse->successful() ) {
            $geminiResponseText = $geminiResponse->object()->candidates[0]->content->parts[0]->text;
        }
        else {
            $geminiResponse->throw();
        }


        $chatBot = User::find(User::CHAT_BOT_ID);
        $channel = Channel::find($request->get('channel_id'));
        $message = [
            'text' => $geminiResponseText,
            'type' => MessageType::find(MessageType::TEXT_ID)
        ];

        $message = $chatBot->sendMessage($channel, $message);

        if ( $message === null ) {
            return response()->json( ['success' => false], 403);
        }

        $members = $channel->members();

        foreach ($members as $member) {
            MessageSent::dispatch($member, $channel, $message);
        }

        $channel->info = $channel->getInfo();
        $message->info = $message->getInfo();

        return response()->json([
            'success' => true,
            'channel' => $channel,
            'message' => $message
        ]);
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
     * Dispatch event when user start writing.
     * @return Illuminate\Http\JsonResponse
     */
    public function userIsWriting(Channel $channel, Request $request): JsonResponse
    {
        $members = $channel->members();
        foreach ($members as $member) {
            $userWriting = $request->has('chat-bot')
                ? User::find( User::CHAT_BOT_ID )
                : auth()->user();
            UserWriting::dispatch($member, $channel, $userWriting, $request->get('is-writing') ?? false);
        }

        return response()->json([
            'success' => true
        ]);
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
     * @return Illuminate\Http\JsonResponse
     */
    public function destroy(Message $message) : JsonResponse
    {

        if ( $message->user->id !== auth()->user()->id ) {
            return response()->json(['success' => false]);
        }

        $message->remove();

        $members = $message->channel->members();
        foreach ($members as $member) {
            MessageDeleted::dispatch($member, $message);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
