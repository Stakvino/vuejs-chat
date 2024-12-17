<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        try {
            $request->authenticate();
        } catch (\Throwable $th) {
            if ( isset($th->status) ) {
                return response()->json([
                    'validation_error' => true,
                    'error_messages' => [
                        'email' => __('auth.failed')
                    ]
                ]);
            }
        }

        $request->session()->regenerate();

        return response()->json([
            'success' => true,
            'redirect' => '/chat'
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'redirect' => '/'
        ]);
    }
}
