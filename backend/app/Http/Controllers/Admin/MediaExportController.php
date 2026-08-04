<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Services\Admin\MediaAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaExportController
{
    public function __invoke(
        Request $request,
        MediaAdminService $mediaService,
        ReportExportService $exportService
    ): StreamedResponse|BinaryFileResponse|Response {
        Gate::authorize('export', Product::class);

        $format = strtolower((string) $request->input('format', 'csv'));

        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'type' => array_filter((array) $request->input('type', [])),
            'source' => array_filter((array) $request->input('source', [])),
            'date_from' => $request->input('date_from'),
            'date_until' => $request->input('date_until'),
        ];

        $rows = $mediaService->exportRows($filters);
        $columns = $this->columns();
        $filename = 'media-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Media Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'name', 'label' => 'File Name'],
            ['name' => 'type', 'label' => 'Type'],
            ['name' => 'source', 'label' => 'Source'],
            ['name' => 'owner', 'label' => 'Owner'],
            ['name' => 'size', 'label' => 'Size'],
            ['name' => 'mime', 'label' => 'MIME Type'],
            ['name' => 'created_at', 'label' => 'Uploaded At'],
        ];
    }
}
