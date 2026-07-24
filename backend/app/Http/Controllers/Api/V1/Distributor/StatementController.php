<?php

namespace App\Http\Controllers\Api\V1\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Distributor\StatementResource;
use App\Services\AuditService;
use App\Services\StatementService;
use App\Traits\RespondsWithJson;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatementController extends Controller
{
    use RespondsWithJson;

    public function __construct(private readonly StatementService $service) {}

    public function index(Request $request): JsonResponse
    {
        $distributor = $request->user()->distributor;

        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : null;
        $to = $request->filled('to') ? Carbon::parse($request->input('to')) : null;

        $statement = $this->service->generate($distributor, $from, $to);

        AuditService::log(
            $request->user(),
            'distributor_statement_viewed',
            $distributor,
            ['from' => $statement['from'], 'to' => $statement['to']],
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new StatementResource($statement)
        );
    }
}
