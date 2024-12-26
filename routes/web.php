<?php

use Illuminate\Support\Facades\Route;

Route::get('/{all}', function () {
    return view('homepage');
})
->where(['all' => '^(?!api|verify-email).*$']);

require __DIR__.'/auth.php';
