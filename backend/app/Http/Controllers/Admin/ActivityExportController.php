<?php

namespace App\Http\Controllers\Admin;

use App\Models\AuditLog;
use App\Services\Admin\ActivityService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Http\Response;

class ActivityExportController
{
    public function __invoke(
        Request $request,
        ActivityService $activityService,
        ReportExportService $exportService
    ): StreamedResponse | BinaryFileResponse | Response {
        Gate::authorize('export', AuditLog::class);

        $format = strtolower((string) $request->input('format', 'csv'));

        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'category' => array_filter((array) $request->input('category', [])),
            'status' => array_filter((array) $request->input('status', [])),
            'module' => array_filter((array) $request->input('module', [])),
            'user' => $request->input('user') ? (int) $request->input('user') : null,
            'date_from' => $request->input('date_from'),
            'date_until' => $request->input('date_until'),
        ];

        $rows = $activityService->forExport($filters);
        $columns = $this->columns();
        $filename = 'activity-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Activity Export', $columns, $rows, $this->periodLabel($filters)),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'date', 'label' => 'Date'],
            ['name' => 'activity', 'label' => 'Activity'],
            ['name' => 'category', 'label' => 'Category'],
            ['name' => 'module', 'label' => 'Module'],
            ['name' => 'user', 'label' => 'User'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'ip_address', 'label' => 'IP Address'],
            ['name' => 'user_agent', 'label' => 'User Agent'],
            ['name' => 'related_entity', 'label' => 'Related Entity'],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function periodLabel(array $filters): ?string
    {
        $from = $filters['date_from'] ?? null;
        $until = $filters['date_until'] ?? null;

        if ($from && $until) {
            return now()->parse($from)->format('M d, Y').' - '.now()->parse($until)->format('M d, Y');
        }

        return null;
    }
}
