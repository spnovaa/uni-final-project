<?php

namespace App\Domains\Gateway\Support;

use Illuminate\Http\JsonResponse;

/**
 * Class OpenAiErrorResponder.
 */
class OpenAiErrorResponder
{
    /**
     * Invalid request.
     * @param string $message
     * @param ?string $param
     * @param ?string $code
     * @param int $status
     * @return JsonResponse
     */
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

    /**
     * Authentication error.
     * @param string $message
     * @param ?string $code
     * @return JsonResponse
     */
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

    /**
     * Authorization error.
     * @param string $message
     * @param ?string $code
     * @return JsonResponse
     */
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

    /**
     * Server error.
     * @param string $message
     * @param ?string $code
     * @param int $status
     * @return JsonResponse
     */
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

    /**
     * Rate limit error.
     * @param string $message
     * @param ?string $code
     * @return JsonResponse
     */
    public static function rateLimitError(string $message, ?string $code = 'rate_limit_exceeded'): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $message,
                'type' => 'rate_limit_error',
                'param' => null,
                'code' => $code,
            ],
        ], 429);
    }

    /**
     * Payment required.
     * @param string $message
     * @param ?string $code
     * @return JsonResponse
     */
    public static function paymentRequired(string $message, ?string $code = 'insufficient_quota'): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $message,
                'type' => 'insufficient_quota',
                'param' => null,
                'code' => $code,
            ],
        ], 402);
    }
}
