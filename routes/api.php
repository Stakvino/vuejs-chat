<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/auth-check', function(){ return auth()->check(); })->name('auth.check');
Route::get('/auth-user', [UserController::class, 'getAuthUser'])
->name('auth.user')->middleware('auth:sanctum');

Route::put('/user/profile/update', [UserController::class, 'updateProfile'])
->name('profile.update')->middleware('auth:sanctum');

Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);
    return ['token' => $token->plainTextToken];
});

Route::get('/get-csrf', function (Request $request) {
    session()->regenerate();
    return response()->json([ "token" => csrf_token() ], 200);
});
