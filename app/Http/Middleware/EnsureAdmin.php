<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\User\AccountRole;
use App\Http\Responses\APIResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return APIResponse::unauthorized();
        }

        if ($user->role !== AccountRole::ADMIN) {
            return APIResponse::forbidden();
        }

        return $next($request);
    }
}
