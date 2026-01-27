<?php

namespace App\Http\Controllers\Api;

use App\Http\Responses\APIResponse;
use App\Http\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\{JsonResponse, Request};

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
