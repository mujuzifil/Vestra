<?php

namespace App\Filament\Pages\Reports;

use App\Filament\Concerns\HasReportFilters;
use App\Services\ReportExportService;
use App\Services\ReportService;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

abstract class ReportPage extends Page implements HasForms
{
    use HasReportFilters;
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Reports';

    protected ReportService $reportService;

    protected ReportExportService $exportService;

    public function mount(): void
    {
        $this->reportService = app(ReportService::class);
        $this->exportService = app(ReportExportService::class);

        $this->mountFiltersForm();
    }

    public function form(Form $form): Form
    {
        return $this->configureFilterForm($form);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => $this->exportCsv()),

            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(fn () => $this->exportService->pdf()),

            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->action(fn () => $this->exportService->excel()),
        ];
    }

    abstract protected function getExportColumns(): array;

    abstract protected function getExportRows(): array;

    abstract protected function getReportSlug(): string;

    protected function exportCsv()
    {
        return $this->exportService->csv(
            $this->getExportFilename($this->getReportSlug()),
            $this->getExportColumns(),
            $this->getExportRows()
        );
    }
}
