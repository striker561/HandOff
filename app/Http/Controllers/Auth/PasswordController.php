<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Validation\Rules;
use App\Http\Responses\APIResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\{JsonResponse, Request};

class PasswordController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make((string) $request->input('password')),
        ]);

        return APIResponse::success('Password updated successfully.');
    }
}
