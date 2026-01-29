<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Unified AI Gateway",
 *     description="OpenAI-compatible API gateway with wallet and subscription management"
 * )
 *
 * @OA\Server(
 *     url="/",
 *     description="Default server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Bearer {token}"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="gateway_key",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="Bearer {API_KEY} for gateway requests (or X-API-Key header)"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="gateway_key_header",
 *     type="apiKey",
 *     in="header",
 *     name="X-API-Key",
 *     description="Raw API key header for gateway requests"
 * )
 */
class DocsController extends Controller
{
}
