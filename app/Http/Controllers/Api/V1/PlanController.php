<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Billing\PlanResource;
use App\Services\Audit\AuditLogServiceInterface;
use App\Services\Billing\Plan\PlanServiceInterface;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct(
        private readonly PlanServiceInterface $plans,
        private readonly AuditLogServiceInterface $audit
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/plans",
     *     summary="List subscription plans",
     *     tags={"Plans"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *          response=200,
     *          description="Plans",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/PlanResource")
     *          )
     *     )
     * )
     */
    public function index()
    {
        return PlanResource::collection($this->plans->listActive());
    }

    /**
     * @OA\Post(
     *     path="/api/v1/plans",
     *     summary="Create a subscription plan",
     *     tags={"Plans"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","price","period"},
     *              @OA\Property(property="name", type="string", example="Starter"),
     *              @OA\Property(property="price", type="number", format="float", example=19.99),
     *              @OA\Property(property="currency", type="string", example="USD"),
     *              @OA\Property(property="period", type="string", example="monthly"),
     *              @OA\Property(property="included_credits", type="number", format="float", example=1000),
     *              @OA\Property(property="rate_limits", type="object", example={"rpm":60}),
     *              @OA\Property(property="features", type="object", example={"priority_support":true}),
     *              @OA\Property(property="status", type="string", example="active")
     *          )
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Plan created",
     *          @OA\JsonContent(ref="#/components/schemas/PlanResource")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'period' => ['required', 'string', 'in:monthly,yearly'],
            'included_credits' => ['nullable', 'numeric', 'min:0'],
            'rate_limits' => ['nullable', 'array'],
            'features' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
        ]);

        $plan = $this->plans->create([
            'name' => $data['name'],
            'price' => $data['price'],
            'currency' => $data['currency'] ?? 'USD',
            'period' => $data['period'],
            'included_credits' => $data['included_credits'] ?? null,
            'rate_limits' => $data['rate_limits'] ?? null,
            'features' => $data['features'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        $this->audit->record($request->user(), 'plan.created', $plan);

        return (new PlanResource($plan))
            ->response()
            ->setStatusCode(201);
    }
}
