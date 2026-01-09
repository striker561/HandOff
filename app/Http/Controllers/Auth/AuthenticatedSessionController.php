<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Responses\APIResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return APIResponse::success(
            'User Data',
            [
                'user' => $request->user(),
            ],
        );
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return APIResponse::noContent('Logged out');
    }
}
