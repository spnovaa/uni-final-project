<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Billing\InvoiceResource;
use App\Jobs\GenerateInvoicePdfJob;
use App\Models\Invoice;
use App\Services\Billing\Invoice\InvoiceServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * API controller for invoice endpoints.
 */
class InvoiceController extends Controller
{
    /**
     * Create a new instance.
     * @param InvoiceServiceInterface $invoices
     * @return void
     */
    public function __construct(private readonly InvoiceServiceInterface $invoices)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/invoices",
     *     summary="List invoices",
     *     tags={"Invoices"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", example=50)),
     *     @OA\Response(
     *          response=200,
     *          description="Invoices list",
     *          @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/InvoiceResource"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $limit = max(1, min(200, (int) $request->query('limit', 50)));
        $invoices = $this->invoices->list($request->user(), $limit);

        return InvoiceResource::collection($invoices);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/invoices/{invoice}",
     *     summary="Get invoice details",
     *     tags={"Invoices"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="invoice", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *          response=200,
     *          description="Invoice",
     *          @OA\JsonContent(ref="#/components/schemas/InvoiceResource")
     *     )
     * )
     */
    public function show(Request $request, Invoice $invoice)
    {
        if ((int) $invoice->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        return new InvoiceResource($invoice->load('items'));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/invoices/{invoice}/pdf",
     *     summary="Download invoice PDF (signed URL)",
     *     tags={"Invoices"},
     *     @OA\Parameter(name="invoice", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *          response=200,
     *          description="PDF file"
     *     ),
     *     @OA\Response(
     *          response=202,
     *          description="PDF generation queued"
     *     )
     * )
     */
    public function pdf(Request $request, Invoice $invoice)
    {
        if ($request->user() && (int) $invoice->user_id !== (int) $request->user()->id) {
            abort(404);
        }

        $path = $this->invoices->pdfPath($invoice);

        if (! Storage::disk('local')->exists($path)) {
            GenerateInvoicePdfJob::dispatch($invoice->id);

            return response()->json([
                'status' => 'processing',
            ], 202);
        }

        return Storage::disk('local')->download($path, $invoice->number.'.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
