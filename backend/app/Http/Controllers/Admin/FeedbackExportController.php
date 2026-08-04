<?php

namespace App\Http\Controllers\Admin;

use App\Models\CustomerFeedback;
use App\Services\Admin\FeedbackAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeedbackExportController
{
    public function __invoke(
        Request $request,
        FeedbackAdminService $feedbackService,
        ReportExportService $exportService
    ): StreamedResponse|BinaryFileResponse|Response {
        Gate::authorize('viewAny', CustomerFeedback::class);

        $format = strtolower((string) $request->input('format', 'csv'));
        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'status' => array_filter((array) $request->input('status', [])),
            'category' => array_filter((array) $request->input('category', [])),
            'priority' => array_filter((array) $request->input('priority', [])),
            'date_from' => $request->input('date_from'),
            'date_until' => $request->input('date_until'),
        ];

        $rows = $feedbackService->exportRows($filters);
        $columns = $this->columns();
        $filename = 'feedback-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Customer Feedback Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'customer', 'label' => 'Customer'],
            ['name' => 'email', 'label' => 'Email'],
            ['name' => 'category', 'label' => 'Category'],
            ['name' => 'subject', 'label' => 'Subject'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'priority', 'label' => 'Priority'],
            ['name' => 'read', 'label' => 'Read'],
            ['name' => 'submitted_at', 'label' => 'Submitted At'],
        ];
    }
}
