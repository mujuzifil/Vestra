<?php

namespace App\Http\Controllers\Admin;

use App\Models\DistributorBranch;
use App\Services\Admin\TerritoryAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TerritoryExportController
{
    public function __invoke(
        Request $request,
        TerritoryAdminService $territoryService,
        ReportExportService $exportService
    ): StreamedResponse | BinaryFileResponse | Response {
        Gate::authorize('export', DistributorBranch::class);

        $format = strtolower((string) $request->input('format', 'csv'));

        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'country' => array_filter((array) $request->input('country', [])),
            'district' => array_filter((array) $request->input('district', [])),
            'status' => array_filter((array) $request->input('status', [])),
            'distributor_id' => $request->input('distributor') ? (int) $request->input('distributor') : null,
        ];

        $rows = $territoryService->exportBranches($filters);
        $columns = $this->columns();
        $filename = 'territories-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Territories Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'name', 'label' => 'Branch Name'],
            ['name' => 'distributor', 'label' => 'Distributor'],
            ['name' => 'manager_name', 'label' => 'Manager'],
            ['name' => 'phone', 'label' => 'Phone'],
            ['name' => 'email', 'label' => 'Email'],
            ['name' => 'country', 'label' => 'Country'],
            ['name' => 'district', 'label' => 'District'],
            ['name' => 'city', 'label' => 'City'],
            ['name' => 'address', 'label' => 'Address'],
            ['name' => 'latitude', 'label' => 'Latitude'],
            ['name' => 'longitude', 'label' => 'Longitude'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'is_default', 'label' => 'Default Branch'],
            ['name' => 'created_at', 'label' => 'Created At'],
        ];
    }
}
