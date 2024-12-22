<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/auth-check', function(){ return auth()->check(); })->name('auth.check');
Route::get('/auth-user', [UserController::class, 'getAuthUser'])->name('auth.user');

Route::put('/user/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
