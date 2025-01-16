<?php

use App\Models\User;
use App\Models\Channel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat-channel.{user}', function (User $user) {
    // return $user->isSubscribedTo($channel);
    return $user->id === auth()->user()->id;
});

Broadcast::channel('message-seen.{user}', function (User $user) {
    return $user->id === auth()->user()->id;
});

Broadcast::channel('channel-created.{user}', function (User $user) {
    return $user->id === auth()->user()->id;
});


