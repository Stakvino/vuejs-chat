<?php

use App\Models\User;
use App\Models\Channel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat-channel.{channel}', function (User $user, Channel $channel) {
    return $user->isSubscribedTo($channel);
});
