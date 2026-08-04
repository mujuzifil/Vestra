<?php

namespace App\Http\Controllers\Admin;

use App\Models\BlogPost;
use App\Services\Admin\BlogAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BlogExportController
{
    public function __invoke(
        Request $request,
        BlogAdminService $blogService,
        ReportExportService $exportService
    ): StreamedResponse|BinaryFileResponse|Response {
        Gate::authorize('export', BlogPost::class);

        $format = strtolower((string) $request->input('format', 'csv'));

        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'status' => array_filter((array) $request->input('status', [])),
            'author' => $request->input('author'),
            'category' => array_filter((array) $request->input('category', [])),
            'date_from' => $request->input('date_from'),
            'date_until' => $request->input('date_until'),
        ];

        $rows = $blogService->exportRows($filters);
        $columns = $this->columns();
        $filename = 'blog-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Blog Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'title', 'label' => 'Title'],
            ['name' => 'slug', 'label' => 'Slug'],
            ['name' => 'author', 'label' => 'Author'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'visibility', 'label' => 'Visibility'],
            ['name' => 'categories', 'label' => 'Categories'],
            ['name' => 'view_count', 'label' => 'Views'],
            ['name' => 'published_at', 'label' => 'Published At'],
            ['name' => 'created_at', 'label' => 'Created At'],
            ['name' => 'updated_at', 'label' => 'Updated At'],
        ];
    }
}
