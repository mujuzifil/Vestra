<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Services\Admin\ProductAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductExportController
{
    public function __invoke(
        Request $request,
        ProductAdminService $productService,
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
            'status' => array_filter((array) $request->input('status', [])),
            'category' => array_filter((array) $request->input('category', [])),
            'stock' => $request->input('stock'),
            'featured' => $request->input('featured'),
        ];

        $rows = $productService->exportRows($filters);
        $columns = $this->columns();
        $filename = 'products-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Products Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'sku', 'label' => 'SKU'],
            ['name' => 'category', 'label' => 'Category'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'featured', 'label' => 'Featured'],
            ['name' => 'price', 'label' => 'Price'],
            ['name' => 'distributor_price', 'label' => 'Distributor Price'],
            ['name' => 'stock_quantity', 'label' => 'Stock'],
            ['name' => 'stock_status', 'label' => 'Stock Status'],
            ['name' => 'created_at', 'label' => 'Created At'],
            ['name' => 'updated_at', 'label' => 'Updated At'],
        ];
    }
}
