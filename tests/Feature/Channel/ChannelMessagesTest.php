<?php

use App\Models\User;
use App\Utils\Helpers;
use App\Models\Channel;
use App\Models\ChannelType;
use App\Models\ChannelUser;
use Illuminate\Http\UploadedFile;

test('users can display the messages of a channel when he clicks on it', function () {

    $user = User::factory()->create();
    $receiver = User::factory()->create();
    ChannelType::create(['name' => 'private', 'id' => ChannelType::PRIVATE_ID]);
    $privateChannel = Channel::factory()->create();

    $user->subscribeTo($privateChannel);
    $receiver->subscribeTo($privateChannel);

    auth()->login($user);

    $user->sendMessage($privateChannel, 'first message');
    $user->sendMessage($privateChannel, 'second message');
    $user->sendMessage($privateChannel, 'third message');

    $response = $this->get('/api/channels/messages/' . $privateChannel->id);

    $response->assertStatus(200);
    expect($response['success'])->toBeTrue();
    expect( sizeof($response['channel']['messages']) )->toBe(3);

});






