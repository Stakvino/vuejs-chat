<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    // return $request->user();
});

Route::get('/auth-check', function() {
    return response()->json(['isAuth' => auth()->check()]);
})->name('auth.check');
Route::get('/auth-user', [UserController::class, 'getAuthUser'])
->name('auth.user')->middleware('auth:sanctum');

Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);
    return ['token' => $token->plainTextToken];
});

Route::get('/get-csrf', function (Request $request) {
    session()->regenerate();
    return response()->json([ "token" => csrf_token() ], 200);
});

Route::prefix('users')->name('users.')
->controller(UserController::class)->group(function () {
    Route::put('/message-event-received/{channel}/{message}', 'messageEventReceived')->name('message-event-received')->middleware(['auth:sanctum', 'verified']);
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/{user}', 'showProfile')->name('show')->middleware(['auth:sanctum', 'verified']);
        Route::put('/update', 'updateProfile')->name('update')->middleware(['auth:sanctum', 'verified']);
    });
});

Route::prefix('channels')->middleware(['auth:sanctum', 'verified'])->name('channels.')
->controller(ChannelController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{channel}', 'show')->name('show');
    Route::get('/messages/{channel}', 'getMessages')->name('get-messages');
    Route::put('/seen/{channel}', 'updateSeen')->name('update-seen');
    Route::get('getinfo/{channel}', 'getInfo')->name('get-info');
});

Route::prefix('messages')->middleware(['auth:sanctum', 'verified'])->name('messages.')
->controller(MessageController::class)->group(function () {
    Route::post('/', 'store')->name('store');
    Route::get('getinfo/{message}', 'getInfo')->name('get-info');
});
