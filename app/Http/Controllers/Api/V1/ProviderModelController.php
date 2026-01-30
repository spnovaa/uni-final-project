<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Gateway\ProviderModelResource;
use App\Models\Provider;
use App\Services\Audit\AuditLogServiceInterface;
use App\Services\Gateway\ProviderModel\ProviderModelServiceInterface;
use Illuminate\Http\Request;

/**
 * API controller for provider model endpoints.
 */
class ProviderModelController extends Controller
{
    /**
     * Create a new instance.
     * @param ProviderModelServiceInterface $models
     * @param AuditLogServiceInterface $audit
     * @return void
     */
    public function __construct(
        private readonly ProviderModelServiceInterface $models,
        private readonly AuditLogServiceInterface $audit
    ) {
    }

    /**
     * List configured models for a provider.
     *
     * Provider model metadata (capabilities/pricing/status) is stored in the DB and may be cached.
     *
     * @OA\Get(
     *     path="/api/v1/providers/{provider}/models",
     *     summary="List models for a provider",
     *     tags={"Providers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *          name="provider",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Provider models",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/ProviderModelResource")
     *          )
     *     )
     * )
     */
    public function index(Provider $provider)
    {
        return ProviderModelResource::collection(
            $this->models->listByProvider($provider->id)
        );
    }

    /**
     * Create a model entry for a provider.
     *
     * Validates input, persists pricing/capabilities configuration, records an audit log, and
     * returns the created provider model resource (201).
     *
     * @OA\Post(
     *     path="/api/v1/providers/{provider}/models",
     *     summary="Create a model for a provider",
     *     tags={"Providers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *          name="provider",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"model_key"},
     *              @OA\Property(property="model_key", type="string", example="gpt-4o-mini"),
     *              @OA\Property(property="capabilities", type="object", example={"chat":true}),
     *              @OA\Property(property="pricing_config", type="object", example={"input_token":0.0005,"output_token":0.001}),
     *              @OA\Property(property="status", type="string", example="active")
     *          )
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Model created",
     *          @OA\JsonContent(ref="#/components/schemas/ProviderModelResource")
     *     )
     * )
     */
    public function store(Request $request, Provider $provider)
    {
        $data = $request->validate([
            'model_key' => ['required', 'string', 'max:255'],
            'capabilities' => ['nullable', 'array'],
            'pricing_config' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $model = $this->models->create([
            'provider_id' => $provider->id,
            'model_key' => $data['model_key'],
            'capabilities' => $data['capabilities'] ?? null,
            'pricing_config' => $data['pricing_config'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        $this->audit->record($request->user(), 'provider_model.created', $model, [
            'provider_id' => $provider->id,
        ]);

        return (new ProviderModelResource($model))
            ->response()
            ->setStatusCode(201);
    }
}
