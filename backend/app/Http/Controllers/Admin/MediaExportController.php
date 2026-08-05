<?php

namespace App\Http\Controllers\Admin;

use App\Models\MediaAsset;
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
        Gate::authorize('export', MediaAsset::class);

        $format = strtolower((string) $request->input('format', 'csv'));

        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'type' => array_filter((array) $request->input('type', [])),
            'usage' => $request->input('usage'),
            'format' => $request->input('format_filter'),
            'uploader_id' => $request->input('uploader'),
            'date_from' => $request->input('date_from'),
            'date_until' => $request->input('date_until'),
        ];

        $rows = $mediaService->exportRows($filters);
        $columns = $this->columns();
        $filename = 'media-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Media Library Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'file_name', 'label' => 'File Name'],
            ['name' => 'original_file_name', 'label' => 'Original Name'],
            ['name' => 'type', 'label' => 'Type'],
            ['name' => 'mime', 'label' => 'MIME Type'],
            ['name' => 'size', 'label' => 'Size'],
            ['name' => 'dimensions', 'label' => 'Dimensions'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'used_in', 'label' => 'Used In'],
            ['name' => 'uploader', 'label' => 'Uploader'],
            ['name' => 'created_at', 'label' => 'Uploaded At'],
            ['name' => 'public_url', 'label' => 'Public URL'],
        ];
    }
}
