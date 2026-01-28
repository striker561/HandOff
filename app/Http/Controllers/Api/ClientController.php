<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\APIResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\Client\{IndexClientRequest, ResendClientInvitationRequest, StoreClientRequest};

class ClientController extends Controller
{
    public function __construct(private readonly ClientService $clients)
    {
    }

    public function index(IndexClientRequest $request): JsonResponse
    {
        $clients = $this->clients->getClients($request->filters());

        return APIResponse::success(
            'Clients retrieved successfully',
            [
                'data' => UserResource::collection($clients),
                'pagination' => [
                    'total' => $clients->total(),
                    'per_page' => $clients->perPage(),
                    'current_page' => $clients->currentPage(),
                    'last_page' => $clients->lastPage(),
                ]
            ]
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