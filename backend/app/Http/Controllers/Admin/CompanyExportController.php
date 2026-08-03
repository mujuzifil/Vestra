<?php

namespace App\Http\Controllers\Admin;

use App\Models\CompanyProfile;
use App\Services\Admin\CompanyService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CompanyExportController
{
    public function __invoke(
        Request $request,
        CompanyService $companyService,
        ReportExportService $exportService
    ): StreamedResponse | BinaryFileResponse | Response {
        Gate::authorize('export', CompanyProfile::class);

        $format = strtolower((string) $request->input('format', 'csv'));

        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'status' => array_filter((array) $request->input('status', [])),
            'industry' => array_filter((array) $request->input('industry', [])),
            'country' => array_filter((array) $request->input('country', [])),
            'region' => array_filter((array) $request->input('region', [])),
            'district' => array_filter((array) $request->input('district', [])),
            'account_manager' => $request->input('account_manager') ? (int) $request->input('account_manager') : null,
            'date_from' => $request->input('date_from'),
            'date_until' => $request->input('date_until'),
            'has_open_quotes' => $request->boolean('has_open_quotes'),
            'has_active_tickets' => $request->boolean('has_active_tickets'),
            'has_distributor' => $request->boolean('has_distributor'),
        ];

        $rows = $companyService->exportCompanies($filters);
        $columns = $this->columns();
        $filename = 'companies-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Companies Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'company_name', 'label' => 'Company Name'],
            ['name' => 'industry', 'label' => 'Industry'],
            ['name' => 'business_type', 'label' => 'Business Type'],
            ['name' => 'country', 'label' => 'Country'],
            ['name' => 'district', 'label' => 'District'],
            ['name' => 'city', 'label' => 'City'],
            ['name' => 'primary_contact_name', 'label' => 'Primary Contact'],
            ['name' => 'primary_contact_email', 'label' => 'Email'],
            ['name' => 'primary_contact_phone', 'label' => 'Phone'],
            ['name' => 'tax_identification', 'label' => 'Tax ID'],
            ['name' => 'registration_number', 'label' => 'Registration Number'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'account_manager', 'label' => 'Account Manager'],
            ['name' => 'region', 'label' => 'Region'],
            ['name' => 'created_at', 'label' => 'Created At'],
        ];
    }
}
