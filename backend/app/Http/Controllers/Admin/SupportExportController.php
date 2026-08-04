<?php

namespace App\Http\Controllers\Admin;

use App\Models\SupportTicket;
use App\Services\Admin\SupportAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportExportController
{
    public function __invoke(
        Request $request,
        SupportAdminService $supportService,
        ReportExportService $exportService
    ): StreamedResponse|BinaryFileResponse|Response {
        Gate::authorize('export', SupportTicket::class);

        $format = strtolower((string) $request->input('format', 'csv'));

        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'status' => array_filter((array) $request->input('status', [])),
            'priority' => array_filter((array) $request->input('priority', [])),
            'enquiry_type' => array_filter((array) $request->input('enquiry_type', [])),
            'assigned_to' => $request->input('assigned_to') ? (int) $request->input('assigned_to') : null,
            'date_from' => $request->input('date_from'),
            'date_until' => $request->input('date_until'),
        ];

        $rows = $supportService->exportRows($filters);
        $columns = $this->columns();
        $filename = 'support-tickets-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Support Tickets Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'reference_number', 'label' => 'Reference'],
            ['name' => 'subject', 'label' => 'Subject'],
            ['name' => 'enquiry_type', 'label' => 'Enquiry Type'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'priority', 'label' => 'Priority'],
            ['name' => 'customer_name', 'label' => 'Customer Name'],
            ['name' => 'customer_email', 'label' => 'Customer Email'],
            ['name' => 'assigned_to', 'label' => 'Assigned To'],
            ['name' => 'resolved_at', 'label' => 'Resolved At'],
            ['name' => 'created_at', 'label' => 'Submitted At'],
        ];
    }
}
