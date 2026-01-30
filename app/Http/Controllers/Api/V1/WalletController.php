<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Billing\WalletResource;
use App\Http\Resources\Billing\WalletTransactionResource;
use App\Services\Billing\Wallet\WalletServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * API controller for wallet endpoints.
 */
class WalletController extends Controller
{
    /**
     * Create a new instance.
     * @param WalletServiceInterface $wallets
     * @return void
     */
    public function __construct(private readonly WalletServiceInterface $wallets)
    {
    }

    /**
     * Get the authenticated user's wallet (creates it on first access).
     *
     * @OA\Get(
     *     path="/api/v1/wallet",
     *     summary="Get current wallet balance",
     *     tags={"Wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *          response=200,
     *          description="Wallet balance",
     *          @OA\JsonContent(ref="#/components/schemas/WalletResource")
     *     )
     * )
     */
    public function show(Request $request)
    {
        $wallet = $this->wallets->getOrCreate($request->user());

        return (new WalletResource($wallet))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Top up wallet balance for the authenticated user.
     *
     * This is a manual top-up endpoint (no payment gateway integration). It creates a wallet
     * transaction and returns both wallet and transaction resources.
     *
     * @OA\Post(
     *     path="/api/v1/wallet/topup",
     *     summary="Top up wallet balance",
     *     tags={"Wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"amount"},
     *              @OA\Property(property="amount", type="number", format="float", example=50),
     *              @OA\Property(property="reason", type="string", example="manual_topup")
     *          )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Wallet updated",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="wallet", ref="#/components/schemas/WalletResource"),
     *              @OA\Property(property="transaction", ref="#/components/schemas/WalletTransactionResource")
     *          )
     *     )
     * )
     */
    public function topup(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $transaction = $this->wallets->topup(
            $request->user(),
            (float) $data['amount'],
            $data['reason'] ?? null
        );

        $wallet = $transaction->wallet()->first();

        return response()->json([
            'wallet' => new WalletResource($wallet),
            'transaction' => new WalletTransactionResource($transaction),
        ]);
    }

    /**
     * List wallet transactions for the authenticated user.
     *
     * Enforces a limit in the service layer.
     *
     * @OA\Get(
     *     path="/api/v1/wallet/transactions",
     *     summary="List wallet transactions",
     *     tags={"Wallet"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=50)
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Wallet transactions",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/WalletTransactionResource")
     *          )
     *     )
     * )
     */
    public function transactions(Request $request)
    {
        $limit = (int) $request->query('limit', 50);

        $transactions = $this->wallets->transactions($request->user(), $limit);

        return WalletTransactionResource::collection($transactions);
    }
}
