<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Responses\APIResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        return APIResponse::success(
            'User retrieved successfully',
            (new UserResource($request->user()))->resolve()
        );
    }
}
