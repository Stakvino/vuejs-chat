<?php

use Illuminate\Support\Facades\Route;

Route::get('/{all}', function () {
    return view('homepage');
})
->where(['all' => '^(?!api).*$']);

require __DIR__.'/auth.php';
