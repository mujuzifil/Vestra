<?php

namespace App\Http\Controllers\Admin;

use App\Services\Admin\RoleAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RoleExportController
{
    public function __invoke(
        Request $request,
        RoleAdminService $roleService,
        ReportExportService $exportService
    ): StreamedResponse|BinaryFileResponse|Response {
        Gate::authorize('export', Role::class);

        $format = strtolower((string) $request->input('format', 'csv'));

        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'type' => array_filter((array) $request->input('type', [])),
        ];

        $rows = $roleService->exportRows($filters);
        $columns = $this->columns();
        $filename = 'roles-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Roles Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'type', 'label' => 'Type'],
            ['name' => 'description', 'label' => 'Description'],
            ['name' => 'users_count', 'label' => 'Users Assigned'],
            ['name' => 'permissions_count', 'label' => 'Permissions'],
            ['name' => 'created_at', 'label' => 'Created At'],
            ['name' => 'updated_at', 'label' => 'Updated At'],
        ];
    }
}
