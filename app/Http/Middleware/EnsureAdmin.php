<?php

namespace App\Http\Middleware;

use App\Enums\User\AccountRole;
use App\Http\Responses\APIResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return APIResponse::unauthorized();
        }

        if ($user->role !== AccountRole::ADMIN) {
            return APIResponse::forbidden();
        }

        return $next($request);
    }
}
