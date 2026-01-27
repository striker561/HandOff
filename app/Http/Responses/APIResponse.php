<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;

class APIResponse
{
    // Default success response
    public static function success(
        string $msg = 'Success',
        array $data = [],
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'msg' => $msg,
            'data' => $data,
        ], $status);
    }

    // Created (201)
    public static function created(
        string $msg = 'Resource created',
        array $data = []
    ): JsonResponse {
        return self::success($msg, $data, 201);
    }

    // No Content (204)
    public static function noContent()
    {
        return response(status: 204);
    }

    // Generic client error (400)
    public static function error(
        string $msg = 'Something went wrong',
        array $errors = [],
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'msg' => $msg,
            'errors' => $errors,
        ], $status);
    }

    // Unauthorized (401)
    public static function unauthorized(string $msg = 'Unauthorized'): JsonResponse
    {
        return self::error($msg, [], 401);
    }

    // Forbidden (403)
    public static function forbidden(string $msg = 'Forbidden'): JsonResponse
    {
        return self::error($msg, [], 403);
    }

    // Not Found (404)
    public static function notFound(string $msg = 'Resource not found'): JsonResponse
    {
        return self::error($msg, [], 404);
    }

    // Validation error (422)
    public static function validation(
        array $errors,
        string $msg = 'Unprocessable entity'
    ): JsonResponse {
        return response()->json([
            'msg' => $msg,
            'errors' => $errors,
        ], 422);
    }

    // Too Many Requests (429)
    public static function tooManyRequests(string $msg = 'Too many requests'): JsonResponse
    {
        return self::error($msg, [], 429);
    }

    // Internal Server Error (500)
    public static function serverError(string $msg = 'Internal server error'): JsonResponse
    {
        return self::error($msg, [], 500);
    }

    // Translate Laravel language keys if needed
    public static function translateStatus(string $status): string
    {
        return Lang::has($status) ? __($status) : $status;
    }
}
