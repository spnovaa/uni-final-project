<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Gateway\ProviderResource;
use App\Services\Audit\AuditLogServiceInterface;
use App\Services\Gateway\Provider\ProviderServiceInterface;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function __construct(
        private readonly ProviderServiceInterface $providers,
        private readonly AuditLogServiceInterface $audit
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/providers",
     *     summary="List AI providers",
     *     tags={"Providers"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *          response=200,
     *          description="Providers",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ProviderResource")
     *          )
     *     )
     * )
     */
    public function index()
    {
        return ProviderResource::collection($this->providers->list());
    }

    /**
     * @OA\Post(
     *     path="/api/v1/providers",
     *     summary="Create an AI provider",
     *     tags={"Providers"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","base_url"},
     *              @OA\Property(property="name", type="string", example="openai"),
     *              @OA\Property(property="type", type="string", example="openai_compatible"),
     *              @OA\Property(property="base_url", type="string", example="https://api.openai.com/v1"),
     *              @OA\Property(property="api_key", type="string", example="sk-..."),
     *              @OA\Property(property="timeout", type="integer", example=60),
     *              @OA\Property(property="status", type="string", example="active"),
     *              @OA\Property(property="priority", type="integer", example=0)
     *          )
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Provider created",
     *          @OA\JsonContent(ref="#/components/schemas/ProviderResource")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:providers,name'],
            'type' => ['nullable', 'string', 'max:50'],
            'base_url' => ['required', 'string', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'timeout' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'priority' => ['nullable', 'integer', 'min:0'],
        ]);

        $provider = $this->providers->create($data);

        $this->audit->record($request->user(), 'provider.created', $provider);

        return (new ProviderResource($provider))
            ->response()
            ->setStatusCode(201);
    }
}
