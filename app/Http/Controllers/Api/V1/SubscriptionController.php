<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Billing\SubscriptionResource;
use App\Services\Billing\Plan\PlanServiceInterface;
use App\Services\Billing\Subscription\SubscriptionServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    public function __construct(
        private readonly SubscriptionServiceInterface $subscriptions,
        private readonly PlanServiceInterface $plans
    ) {
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscriptions",
     *     summary="Create a subscription for the current user",
     *     tags={"Subscriptions"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"plan_id"},
     *              @OA\Property(property="plan_id", type="integer", example=1)
     *          )
     *     ),
     *     @OA\Response(
     *          response=201,
     *          description="Subscription created",
     *          @OA\JsonContent(ref="#/components/schemas/SubscriptionResource")
     *     )
     * )
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'plan_id' => ['required', 'integer'],
        ]);

        $plan = $this->plans->findOrFail((int) $data['plan_id']);
        $subscription = $this->subscriptions->subscribe($request->user(), $plan);
        $subscription->load('plan');

        return (new SubscriptionResource($subscription))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/subscriptions/current",
     *     summary="Get current active subscription",
     *     tags={"Subscriptions"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *          response=200,
     *          description="Current subscription",
     *          @OA\JsonContent(ref="#/components/schemas/SubscriptionResource")
     *     ),
     *     @OA\Response(response=404, description="No active subscription")
     * )
     */
    public function current(Request $request)
    {
        $subscription = $this->subscriptions->current($request->user());

        if (! $subscription) {
            return response()->json(['message' => 'No active subscription.'], 404);
        }

        $subscription->load('plan');

        return new SubscriptionResource($subscription);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscriptions/cancel",
     *     summary="Cancel current subscription",
     *     tags={"Subscriptions"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *          response=200,
     *          description="Subscription canceled",
     *          @OA\JsonContent(ref="#/components/schemas/SubscriptionResource")
     *     ),
     *     @OA\Response(response=404, description="No active subscription")
     * )
     */
    public function cancel(Request $request)
    {
        $subscription = $this->subscriptions->cancel($request->user());

        if (! $subscription) {
            return response()->json(['message' => 'No active subscription.'], 404);
        }

        $subscription->load('plan');

        return new SubscriptionResource($subscription);
    }
}
