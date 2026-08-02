<?php

namespace App\Filament\Pages\Reports;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\Cache;

class ProcurementReport extends ReportPage
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Procurement';

    protected static ?int $navigationSort = 50;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.reports.procurement-report';

    public function getTitle(): string
    {
        return 'Procurement Report';
    }

    public function getOpenOrdersCount(): int
    {
        return Cache::remember('admin.reports.procurement.open_orders', 300, function (): int {
            return PurchaseOrder::open()->count();
        });
    }

    public function getTotalCommittedSpend(): float
    {
        return Cache::remember('admin.reports.procurement.committed_spend', 300, function (): float {
            return PurchaseOrder::query()
                ->whereIn('status', [PurchaseOrderStatus::ORDERED->value, PurchaseOrderStatus::PARTIAL->value])
                ->sum('total') ?? 0;
        });
    }

    public function getSupplierCount(): int
    {
        return Cache::remember('admin.reports.procurement.supplier_count', 300, function (): int {
            return Supplier::active()->count();
        });
    }

    public function getRecentPurchaseOrders(): array
    {
        return PurchaseOrder::query()
            ->with(['supplier', 'warehouse'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (PurchaseOrder $po) => [
                'po_number' => $po->po_number,
                'supplier' => $po->supplier?->name,
                'status' => $po->status->label(),
                'total' => $po->total,
                'expected_at' => $po->expected_at?->format('M d, Y'),
            ])
            ->toArray();
    }

    protected function getReportSlug(): string
    {
        return 'procurement';
    }

    protected function getExportColumns(): array
    {
        return [
            ['name' => 'po_number', 'label' => 'PO Number'],
            ['name' => 'supplier', 'label' => 'Supplier'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'total', 'label' => 'Total (UGX)'],
            ['name' => 'expected_at', 'label' => 'Expected'],
        ];
    }

    protected function getExportRows(): array
    {
        return $this->getRecentPurchaseOrders();
    }
}
