<?php

use App\Models\User;
use App\Utils\Helpers;
use App\Models\Channel;
use App\Models\ChannelType;
use App\Models\ChannelUser;
use Illuminate\Http\UploadedFile;

test('users can send a message in existing channels or create one if necessary', function () {

    $user = User::factory()->create();
    $receiver = User::factory()->create();
    ChannelType::create(['name' => 'private', 'id' => ChannelType::PRIVATE_ID]);
    ChannelType::create(['name' => 'public', 'id' => ChannelType::PUBLIC_ID]);
    $privateChannel = Channel::factory()->create();
    $publicChannel = Channel::factory()->create(['channel_type_id' => ChannelType::PUBLIC_ID]);

    $user->subscribeTo($publicChannel);
    $user->subscribeTo($privateChannel);
    $receiver->subscribeTo($publicChannel);
    $receiver->subscribeTo($privateChannel);

    auth()->login($user);

    $privateMessage = $user->sendMessage($privateChannel, 'Message');
    $publicMessage = $user->sendMessage($publicChannel, 'Message');

    expect( $privateMessage->isSeenBy($receiver) )->toBeFalse();
    expect( $publicMessage->isSeenBy($receiver) )->toBeFalse();

});






