<?php

namespace App\Http\Controllers\Admin;

use App\Models\Distributor;
use App\Services\Admin\PartnerAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PartnerExportController
{
    public function __invoke(
        Request $request,
        PartnerAdminService $partnerService,
        ReportExportService $exportService
    ): StreamedResponse|BinaryFileResponse|Response {
        Gate::authorize('export', Distributor::class);

        $format = strtolower((string) $request->input('format', 'csv'));

        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'status' => array_filter((array) $request->input('status', [])),
            'country' => array_filter((array) $request->input('country', [])),
            'region' => array_filter((array) $request->input('region', [])),
            'sales_rep' => $request->input('sales_rep') ? (int) $request->input('sales_rep') : null,
        ];

        $rows = $partnerService->exportPartners($filters);
        $columns = $this->columns();
        $filename = 'active-partners-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Active Partners Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'company_name', 'label' => 'Company Name'],
            ['name' => 'trading_name', 'label' => 'Trading Name'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'business_type', 'label' => 'Partner Type'],
            ['name' => 'country', 'label' => 'Country'],
            ['name' => 'district', 'label' => 'District'],
            ['name' => 'city', 'label' => 'City'],
            ['name' => 'email', 'label' => 'Email'],
            ['name' => 'phone', 'label' => 'Phone'],
            ['name' => 'sales_rep', 'label' => 'Sales Rep'],
            ['name' => 'credit_limit', 'label' => 'Credit Limit'],
            ['name' => 'credit_balance', 'label' => 'Credit Balance'],
            ['name' => 'registration_number', 'label' => 'Registration Number'],
            ['name' => 'created_at', 'label' => 'Created At'],
        ];
    }
}
