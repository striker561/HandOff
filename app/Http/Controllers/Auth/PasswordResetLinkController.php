<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Responses\APIResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Would send email if it exists 
        Password::sendResetLink(
            $request->only('email')
        );

        // Always return 200 anti-enumeration
        return APIResponse::success(
            'We will send an email if the user with this email exists.'
        );
    }
}
