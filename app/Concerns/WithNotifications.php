<?php

namespace App\Concerns;

use Flux\Flux;

trait WithNotifications
{
    /**
     * Show a success toast notification.
     */
    public function notifySuccess(string $message, int $duration = 5000): void
    {
        Flux::toast(variant: 'success', text: $message, duration: $duration);
    }

    /**
     * Show an error toast notification.
     */
    public function notifyError(string $message, int $duration = 5000): void
    {
        Flux::toast(variant: 'danger', text: $message, duration: $duration);
    }

    /**
     * Show a warning toast notification.
     */
    public function notifyWarning(string $message, int $duration = 5000): void
    {
        Flux::toast(variant: 'warning', text: $message, duration: $duration);
    }

    /**
     * Show an info toast notification.
     */
    public function notifyInfo(string $message, int $duration = 5000): void
    {
        Flux::toast(variant: 'info', text: $message, duration: $duration);
    }

    /**
     * Show a toast notification with a custom variant.
     */
    public function notify(string $message, string $variant = 'success', int $duration = 5000): void
    {
        Flux::toast(variant: $variant, text: $message, duration: $duration);
    }
}
