<?php

use App\Models\User;
use App\Utils\Helpers;
use App\Models\Channel;
use App\Models\ChannelType;
use App\Models\ChannelUser;
use Illuminate\Http\UploadedFile;

test('users can display the list of channels hes subscribed to', function () {

    $user = User::factory()->create();
    $sender = User::factory()->create();
    $publicChannel = Channel::create([
        'id' => 1,
        'channel_type_id' => ChannelType::PUBLIC_ID,
        'name' => 'Public Channel'
    ]);
    $privateChannel = Channel::factory()->create();
    $user->subscribeTo($publicChannel);
    $user->subscribeTo($privateChannel);
    $sender->subscribeTo($publicChannel);
    $sender->subscribeTo($privateChannel);

    auth()->login($user);

    $response = $this->get('/api/channels');
    $response->assertStatus(200);
    expect($response['success'])->toBeTrue();
    expect( sizeof($response['channels']['private']) )->toBe(1);
    expect( sizeof($response['channels']['public']) )->toBe(1);

});






