<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Billing\InvoiceResource;
use App\Http\Resources\Reporting\UsageReportResource;
use App\Http\Resources\Reporting\WalletLedgerResource;
use App\Services\Reporting\ReportingServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * API controller for report endpoints.
 */
class ReportController extends Controller
{
    /**
     * Create a new instance.
     * @param ReportingServiceInterface $reports
     * @return void
     */
    public function __construct(private readonly ReportingServiceInterface $reports)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/usage",
     *     summary="Usage report",
     *     tags={"Reports"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="group_by", in="query", required=false, @OA\Schema(type="string", example="day")),
     *     @OA\Response(
     *          response=200,
     *          description="Usage report",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/UsageReportResource"))
     *     )
     * )
     */
    public function usage(Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'group_by' => ['nullable', Rule::in(['day', 'key', 'provider'])],
        ]);

        $from = $data['from'] ?? now()->subDays(30)->toDateString();
        $to = $data['to'] ?? now()->toDateString();
        $groupBy = $data['group_by'] ?? 'day';

        $rows = $this->reports->usageReport($request->user(), $from, $to, $groupBy);

        return UsageReportResource::collection($rows);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/wallet-ledger",
     *     summary="Wallet ledger report",
     *     tags={"Reports"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Response(
     *          response=200,
     *          description="Wallet ledger entries",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/WalletLedgerResource"))
     *     )
     * )
     */
    public function walletLedger(Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $from = $data['from'] ?? now()->subDays(30)->toDateString();
        $to = $data['to'] ?? now()->toDateString();

        $rows = $this->reports->walletLedger($request->user(), $from, $to);

        return WalletLedgerResource::collection($rows);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/invoices",
     *     summary="Invoice report",
     *     tags={"Reports"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", example="paid")),
     *     @OA\Response(
     *          response=200,
     *          description="Invoices",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/InvoiceResource"))
     *     )
     * )
     */
    public function invoices(Request $request)
    {
        $data = $request->validate([
            'status' => ['nullable', 'string', Rule::in(['draft', 'issued', 'paid', 'void'])],
        ]);

        $rows = $this->reports->invoicesReport($request->user(), $data['status'] ?? null);

        return InvoiceResource::collection($rows);
    }
}
