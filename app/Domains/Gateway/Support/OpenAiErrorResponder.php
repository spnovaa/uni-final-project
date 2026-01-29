<?php

namespace App\Domains\Gateway\Support;

use Illuminate\Http\JsonResponse;

class OpenAiErrorResponder
{
    public static function invalidRequest(string $message, ?string $param = null, ?string $code = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $message,
                'type' => 'invalid_request_error',
                'param' => $param,
                'code' => $code,
            ],
        ], $status);
    }

    public static function authenticationError(string $message, ?string $code = 'invalid_api_key'): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $message,
                'type' => 'authentication_error',
                'param' => null,
                'code' => $code,
            ],
        ], 401);
    }

    public static function authorizationError(string $message, ?string $code = 'insufficient_permissions'): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $message,
                'type' => 'permission_error',
                'param' => null,
                'code' => $code,
            ],
        ], 403);
    }

    public static function serverError(string $message, ?string $code = null, int $status = 502): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $message,
                'type' => 'server_error',
                'param' => null,
                'code' => $code,
            ],
        ], $status);
    }
}
