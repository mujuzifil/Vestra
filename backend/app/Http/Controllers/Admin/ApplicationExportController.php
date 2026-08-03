<?php

namespace App\Http\Controllers\Admin;

use App\Models\DistributorRequest;
use App\Services\Admin\ApplicationAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationExportController
{
    public function __invoke(
        Request $request,
        ApplicationAdminService $applicationService,
        ReportExportService $exportService
    ): StreamedResponse|BinaryFileResponse|Response {
        Gate::authorize('export', DistributorRequest::class);

        $format = strtolower((string) $request->input('format', 'csv'));

        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'status' => array_filter((array) $request->input('status', [])),
            'priority' => array_filter((array) $request->input('priority', [])),
            'country' => array_filter((array) $request->input('country', [])),
            'region' => array_filter((array) $request->input('region', [])),
            'assigned_to' => $request->input('assigned_to') ? (int) $request->input('assigned_to') : null,
            'date_from' => $request->input('date_from'),
            'date_until' => $request->input('date_until'),
        ];

        $rows = $applicationService->exportRows($filters);
        $columns = $this->columns();
        $filename = 'applications-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Distributor Applications Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'company_name', 'label' => 'Company Name'],
            ['name' => 'business_type', 'label' => 'Business Type'],
            ['name' => 'contact_person', 'label' => 'Contact Person'],
            ['name' => 'email', 'label' => 'Email'],
            ['name' => 'phone', 'label' => 'Phone'],
            ['name' => 'country', 'label' => 'Country'],
            ['name' => 'region', 'label' => 'Region'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'priority', 'label' => 'Priority'],
            ['name' => 'assigned_to', 'label' => 'Assigned To'],
            ['name' => 'estimated_volume', 'label' => 'Estimated Volume'],
            ['name' => 'existing_customer', 'label' => 'Existing Customer'],
            ['name' => 'created_at', 'label' => 'Submitted At'],
        ];
    }
}
