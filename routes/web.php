<?php

use Illuminate\Support\Facades\Route;

// Route::group(['domain' => 'vuejschat.oussama-cheriguene.com'], function () {

    Route::get('/{all}', function () {
        return view('homepage');
    })
    ->where(['all' => '^(?!api|verify-email).*$']);

    Route::get('/api/test/email', function () {

    });

// });


require __DIR__.'/auth.php';
