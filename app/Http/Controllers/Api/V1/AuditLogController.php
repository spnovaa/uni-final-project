<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Audit\AuditLogResource;
use App\Services\Audit\AuditLogServiceInterface;
use Illuminate\Http\Request;

/**
 * API controller for audit log endpoints.
 */
class AuditLogController extends Controller
{
    /**
     * Create a new instance.
     * @param AuditLogServiceInterface $logs
     * @return void
     */
    public function __construct(private readonly AuditLogServiceInterface $logs)
    {
    }

    /**
     * List audit log entries.
     *
     * Applies optional filters and enforces a maximum page size for performance.
     *
     * @OA\Get(
     *     path="/api/v1/audit-logs",
     *     operationId="listAuditLogs",
     *     tags={"Audit"},
     *     summary="List audit log entries",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="actor_user_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="action", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="target_type", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="target_id", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="limit", in="query", required=false, @OA\Schema(type="integer", example=50)),
     *     @OA\Response(
     *         response=200,
     *         description="Audit logs list",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/AuditLogResource"))
     *     )
     * )
     */
    public function index(Request $request)
    {
        $filters = $request->only(['actor_user_id', 'action', 'target_type', 'target_id']);
        $limit = max(1, min(200, (int) $request->query('limit', 50)));

        $logs = $this->logs->list($filters, $limit);

        return response()->json(AuditLogResource::collection($logs));
    }
}
