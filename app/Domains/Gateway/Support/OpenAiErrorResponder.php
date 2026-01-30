<?php

namespace App\Domains\Gateway\Support;

use Illuminate\Http\JsonResponse;

/**
 * Helper for building OpenAI-compatible error responses.
 *
 * Centralizes error shapes and HTTP status codes so gateway middleware/pipes can return consistent
 * responses that match the OpenAI API error format.
 */
class OpenAiErrorResponder
{
    /**
     * Build an OpenAI-style `invalid_request_error` response.
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
     * Build an OpenAI-style `authentication_error` response (401).
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
     * Build an OpenAI-style permission error response (403).
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
     * Build an OpenAI-style server error response (defaults to 502).
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
     * Build an OpenAI-style rate limit error response (429).
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
     * Build an OpenAI-style insufficient quota/payment required response (402).
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
