<?php

namespace App\Http\Controllers\Admin;

use App\Models\ContactMessage;
use App\Services\Admin\EnquiryAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnquiryExportController
{
    public function __invoke(
        Request $request,
        EnquiryAdminService $enquiryService,
        ReportExportService $exportService
    ): StreamedResponse|BinaryFileResponse|Response {
        Gate::authorize('export', ContactMessage::class);

        $format  = strtolower((string) $request->input('format', 'csv'));
        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search'       => $request->input('search'),
            'status'       => array_filter((array) $request->input('status', [])),
            'source'       => array_filter((array) $request->input('source', [])),
            'enquiry_type' => array_filter((array) $request->input('enquiry_type', [])),
            'priority'     => array_filter((array) $request->input('priority', [])),
            'assigned_to'  => $request->input('assigned_to') ? (int) $request->input('assigned_to') : null,
            'date_from'    => $request->input('date_from'),
            'date_until'   => $request->input('date_until'),
        ];

        $rows     = $enquiryService->exportRows($filters);
        $columns  = $this->columns();
        $filename = 'enquiries-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv'   => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf'   => $exportService->pdf($filename, 'Enquiries Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'name',         'label' => 'Name'],
            ['name' => 'company',      'label' => 'Company'],
            ['name' => 'email',        'label' => 'Email'],
            ['name' => 'phone',        'label' => 'Phone'],
            ['name' => 'subject',      'label' => 'Subject'],
            ['name' => 'enquiry_type', 'label' => 'Enquiry Type'],
            ['name' => 'status',       'label' => 'Status'],
            ['name' => 'priority',     'label' => 'Priority'],
            ['name' => 'source',       'label' => 'Source'],
            ['name' => 'assigned_to',  'label' => 'Assigned To'],
            ['name' => 'replied_at',   'label' => 'Replied At'],
            ['name' => 'created_at',   'label' => 'Received At'],
        ];
    }
}
