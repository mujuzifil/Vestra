<?php

namespace App\Http\Controllers\Admin;

use App\Models\CreditAccount;
use App\Services\Admin\CreditAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CreditExportController
{
    public function __invoke(
        Request $request,
        CreditAdminService $creditService,
        ReportExportService $exportService
    ): StreamedResponse|BinaryFileResponse|Response {
        Gate::authorize('export', CreditAccount::class);

        $format = strtolower((string) $request->input('format', 'csv'));

        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'status' => array_filter((array) $request->input('status', [])),
            'country' => array_filter((array) $request->input('country', [])),
        ];

        $rows = $creditService->exportAccounts($filters);
        $columns = $this->columns();
        $filename = 'credit-accounts-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Credit Accounts Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'distributor', 'label' => 'Distributor'],
            ['name' => 'country', 'label' => 'Country'],
            ['name' => 'limit', 'label' => 'Credit Limit'],
            ['name' => 'balance', 'label' => 'Outstanding Balance'],
            ['name' => 'authorized_amount', 'label' => 'Authorized Amount'],
            ['name' => 'available_credit', 'label' => 'Available Credit'],
            ['name' => 'utilization_percentage', 'label' => 'Utilization %'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'updated_at', 'label' => 'Updated At'],
        ];
    }
}
