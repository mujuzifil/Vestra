<?php

namespace App\Http\Controllers\Admin;

use App\Models\Task;
use App\Services\Admin\TaskService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskExportController
{
    public function __invoke(
        Request $request,
        TaskService $taskService,
        ReportExportService $exportService
    ): StreamedResponse|BinaryFileResponse|Response {
        Gate::authorize('export', Task::class);

        $format = strtolower((string) $request->input('format', 'csv'));
        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'status' => array_filter((array) $request->input('status', [])),
            'priority' => array_filter((array) $request->input('priority', [])),
            'assignee' => $request->filled('assignee') ? (int) $request->input('assignee') : null,
            'due_from' => $request->input('due_from'),
            'due_until' => $request->input('due_until'),
        ];

        $sort = (string) $request->input('sort', 'due_date');
        $direction = (string) $request->input('direction', 'asc');

        $rows = $taskService->exportRows($filters, $sort, $direction);
        $columns = [
            ['name' => 'title', 'label' => 'Title'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'priority', 'label' => 'Priority'],
            ['name' => 'assignee', 'label' => 'Assignee'],
            ['name' => 'creator', 'label' => 'Created By'],
            ['name' => 'due_date', 'label' => 'Due Date'],
            ['name' => 'completed_at', 'label' => 'Completed At'],
            ['name' => 'created_at', 'label' => 'Created'],
        ];
        $filename = 'tasks-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Tasks Export', $columns, $rows),
        };
    }
}
