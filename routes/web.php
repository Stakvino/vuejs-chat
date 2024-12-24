<?php

use Illuminate\Support\Facades\Route;

Route::get('/{all}', function () {
    return view('homepage');
})
->where(['all' => '^(?!api|test).*$']);

Route::get('/test', function() {
    $a = event(new \App\Events\OrderShipmentStatusUpdated(auth()->user()));
    $b = \App\Events\OrderShipmentStatusUpdated::dispatch(auth()->user());
    \App\Events\TestEvent::dispatch();
});

require __DIR__.'/auth.php';
