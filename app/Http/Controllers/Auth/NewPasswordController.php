<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Responses\APIResponse;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\{Hash, Password};
use Illuminate\Validation\Rules\Password as RulesPassword;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', RulesPassword::defaults()],
        ]);

        $credentials = $request->only('email', 'password', 'token');

        $status = Password::reset(
            $credentials,
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->string('password')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // WE do not want to expose what went wrong rather we wanna still maintain the enumeration protection
        // EMAIL DOES NOT EXIST, they don't need to know that
        // IF THEY DO EVERY THING RIGHT, THEY SHOULD LOGIN
        if ($status != Password::PASSWORD_RESET) {
            return APIResponse::validation(
                ['token' => ['Unable to reset password. Link invalid or expired.']]
            );
        }

        return APIResponse::success(trans($status));
    }
}
