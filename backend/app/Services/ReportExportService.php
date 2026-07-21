<?php

namespace App\Services;

use Filament\Notifications\Notification;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Stream a CSV export from column definitions and row data.
     *
     * @param  string  $filename  The downloaded filename (without extension).
     * @param  array<int, array{name: string, label: string}>  $columns
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function csv(string $filename, array $columns, array $rows): StreamedResponse
    {
        $headers = array_map(fn (array $column): string => $column['label'] ?? $column['name'] ?? '', $columns);

        return ResponseFacade::stream(function () use ($headers, $columns, $rows): void {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                $line = [];
                foreach ($columns as $column) {
                    $key = $column['name'] ?? $column['key'] ?? null;
                    $value = $key ? data_get($row, $key) : null;

                    if (is_array($value)) {
                        $value = json_encode($value);
                    }

                    $line[] = $value ?? '';
                }
                fputcsv($handle, $line);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ]);
    }

    /**
     * Placeholder for PDF export.
     */
    public function pdf(): Response
    {
        Notification::make()
            ->title('PDF export')
            ->body('PDF report export will be available in a future release.')
            ->info()
            ->send();

        return ResponseFacade::noContent();
    }

    /**
     * Placeholder for Excel export.
     */
    public function excel(): Response
    {
        Notification::make()
            ->title('Excel export')
            ->body('Excel report export will be available in a future release.')
            ->info()
            ->send();

        return ResponseFacade::noContent();
    }
}
