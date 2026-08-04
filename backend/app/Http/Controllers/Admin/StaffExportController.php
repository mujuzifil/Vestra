<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Services\Admin\StaffAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffExportController
{
    public function __invoke(
        Request $request,
        StaffAdminService $staffService,
        ReportExportService $exportService
    ): StreamedResponse|BinaryFileResponse|Response {
        Gate::authorize('export', User::class);

        $format = strtolower((string) $request->input('format', 'csv'));
        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'status' => array_filter((array) $request->input('status', [])),
            'role' => array_filter((array) $request->input('role', [])),
        ];

        $rows = $staffService->exportRows($filters);
        $columns = [
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'email', 'label' => 'Email'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'roles', 'label' => 'Roles'],
            ['name' => 'last_login_at', 'label' => 'Last Login'],
            ['name' => 'password_reset_pending', 'label' => 'Password Reset Pending'],
            ['name' => 'created_at', 'label' => 'Joined'],
        ];
        $filename = 'staff-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Staff Export', $columns, $rows),
        };
    }
}
