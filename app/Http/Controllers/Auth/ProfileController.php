<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Validation\Rule;
use App\Http\Responses\APIResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\{JsonResponse, Request};

class ProfileController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:190'],
            'email' => [
                'sometimes',
                'email:rfc,dns',
                'max:190',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->fill($validated);

        // If email changed, you may want to reset verification:
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return APIResponse::success('Profile updated successfully.', new UserResource($user));
    }
}
