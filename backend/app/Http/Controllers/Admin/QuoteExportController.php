<?php

namespace App\Http\Controllers\Admin;

use App\Models\QuoteRequest;
use App\Services\Admin\QuoteAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuoteExportController
{
    public function __invoke(
        Request $request,
        QuoteAdminService $quoteService,
        ReportExportService $exportService
    ): StreamedResponse|BinaryFileResponse|Response {
        Gate::authorize('export', QuoteRequest::class);

        $format = strtolower((string) $request->input('format', 'csv'));

        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'status' => array_filter((array) $request->input('status', [])),
            'priority' => array_filter((array) $request->input('priority', [])),
            'district' => array_filter((array) $request->input('district', [])),
            'city' => array_filter((array) $request->input('city', [])),
            'assigned_to' => $request->input('assigned_to') ? (int) $request->input('assigned_to') : null,
            'date_from' => $request->input('date_from'),
            'date_until' => $request->input('date_until'),
            'close_from' => $request->input('close_from'),
            'close_until' => $request->input('close_until'),
            'min_value' => $request->input('min_value'),
            'max_value' => $request->input('max_value'),
        ];

        $rows = $quoteService->exportQuotes($filters);
        $columns = $this->columns();
        $filename = 'quotes-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Quotes Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'reference_number', 'label' => 'Quote #'],
            ['name' => 'company_name', 'label' => 'Company'],
            ['name' => 'contact_name', 'label' => 'Contact'],
            ['name' => 'email', 'label' => 'Email'],
            ['name' => 'phone', 'label' => 'Phone'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'priority', 'label' => 'Priority'],
            ['name' => 'estimated_value', 'label' => 'Estimated Value'],
            ['name' => 'expected_close_date', 'label' => 'Expected Close'],
            ['name' => 'sales_rep', 'label' => 'Sales Rep'],
            ['name' => 'district', 'label' => 'District'],
            ['name' => 'city', 'label' => 'City'],
            ['name' => 'products', 'label' => 'Products'],
            ['name' => 'created_at', 'label' => 'Created At'],
        ];
    }
}
