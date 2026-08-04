<?php

namespace App\Http\Controllers\Admin;

use App\Models\ProductWarehouseStock;
use App\Services\Admin\InventoryAdminService;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryExportController
{
    public function __invoke(
        Request $request,
        InventoryAdminService $inventoryService,
        ReportExportService $exportService
    ): StreamedResponse|BinaryFileResponse|Response {
        Gate::authorize('export', ProductWarehouseStock::class);

        $format = strtolower((string) $request->input('format', 'csv'));
        $allowed = ['csv', 'excel', 'pdf'];

        if (! in_array($format, $allowed, true)) {
            abort(400, 'Unsupported export format.');
        }

        $filters = [
            'search' => $request->input('search'),
            'warehouse' => array_filter((array) $request->input('warehouse', [])),
            'category' => array_filter((array) $request->input('category', [])),
            'stock_status' => array_filter((array) $request->input('stock_status', [])),
            'date_from' => $request->input('date_from'),
            'date_until' => $request->input('date_until'),
        ];

        $rows = $inventoryService->exportRows($filters);
        $columns = $this->columns();
        $filename = 'inventory-export-'.now()->format('Y-m-d-His');

        return match ($format) {
            'csv' => $exportService->csv($filename, $columns, $rows),
            'excel' => $exportService->excel($filename, $columns, $rows),
            'pdf' => $exportService->pdf($filename, 'Inventory Export', $columns, $rows),
        };
    }

    /**
     * @return array<int, array{name: string, label: string}>
     */
    private function columns(): array
    {
        return [
            ['name' => 'product', 'label' => 'Product'],
            ['name' => 'sku', 'label' => 'SKU'],
            ['name' => 'category', 'label' => 'Category'],
            ['name' => 'warehouse', 'label' => 'Warehouse'],
            ['name' => 'warehouse_code', 'label' => 'Warehouse Code'],
            ['name' => 'quantity', 'label' => 'Quantity'],
            ['name' => 'reserved', 'label' => 'Reserved'],
            ['name' => 'available', 'label' => 'Available'],
            ['name' => 'reorder_level', 'label' => 'Reorder Level'],
            ['name' => 'value', 'label' => 'Value'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'updated_at', 'label' => 'Updated At'],
        ];
    }
}
