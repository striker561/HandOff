<?php

use Illuminate\Http\Request;
use App\Http\Responses\APIResponse;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);
        $middleware->alias([
            'ensureAdmin' => EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // DEFINING CUSTOM EXCEPTION HANDLING IN API TO ENSURE CONSISTENCY AND AVOID FRAMEWORK DRAMA
    
        // 401 — Unauthenticated
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return APIResponse::unauthorized();
            }
            return null;
        });

        // 403 — Forbidden (authenticated but not allowed)
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson()) {
                return APIResponse::forbidden();
            }
            return null;
        });

        // 404 — Route not found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return APIResponse::notFound();
            }
            return null;
        });

        // 404 — Model not found (route model binding)
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return APIResponse::notFound();
            }
            return null;
        });

        // 422 — Validation
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return APIResponse::validation($e->errors(), 'Unprocessable entity');
            }
            return null;
        });

        // 429 — Too many requests
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson()) {
                $headers = $e->getHeaders();
                $seconds = isset($headers['Retry-After'])
                    ? (int) $headers['Retry-After']
                    : 60;

                return APIResponse::tooManyRequests(
                    "Too many attempts. Please try again in {$seconds}s.",
                );
            }
            return null;
        });


        // 500 — Fallback (don’t leak internals)
        $exceptions->render(function (Throwable $_, Request $request) {
            if (!app()->environment('production')) {
                return null;
            }

            if ($request->expectsJson()) {
                return APIResponse::serverError();
            }

            return null;
        });

    })->create();
