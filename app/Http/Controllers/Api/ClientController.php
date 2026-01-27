<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\APIResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\Client\{ResendClientInvitationRequest, StoreClientRequest};

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clients)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $clients = $this->clients->getClients();

        return APIResponse::success(
            'Clients retrieved successfully',
            UserResource::collection($clients)
        );
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $client = $this->clients->createClient($request->validated());

        return APIResponse::created(
            'Client Created and invitation sent',
            (new UserResource($client))
        );
    }

    public function resendInvitation(ResendClientInvitationRequest $request, User $client): JsonResponse
    {

        $this->clients->resendInvitation($client);

        return APIResponse::success(
            'Invitation sent',
            (new UserResource($client->fresh()))
        );
    }
}