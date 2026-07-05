<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

trait WithActionRateLimiting
{
    protected function attemptRateLimitedAction(
        string $action,
        int $maxAttempts = 5,
        int $decaySeconds = 60,
    ): bool {
        $key = $action.':'.Auth::user()->unique_id;

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return false;
        }

        RateLimiter::hit($key, $decaySeconds);

        return true;
    }
}
