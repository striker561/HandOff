<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Responses\APIResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            return APIResponse::validation([
                'email' => [__($status)],
            ]);
        }

        return APIResponse::success(__($status));
    }
}
