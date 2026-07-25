<?php

namespace App\Services;

use App\Exports\ArrayExport;
use Filament\Notifications\Notification;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
     * Export a report as a branded PDF document.
     *
     * @param  string  $filename
     * @param  string  $title
     * @param  array<int, array{name: string, label: string}>  $columns
     * @param  array<int, array<string, mixed>>  $rows
     * @param  string|null  $period
     */
    public function pdf(string $filename, string $title, array $columns, array $rows, ?string $period = null): Response
    {
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('reports.pdf.default', [
            'title' => $title,
            'columns' => $columns,
            'rows' => $rows,
            'period' => $period,
        ]);

        return $pdf->download("{$filename}.pdf");
    }

    /**
     * Export a report as an Excel workbook.
     *
     * @param  string  $filename
     * @param  array<int, array{name: string, label: string}>  $columns
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function excel(string $filename, array $columns, array $rows): BinaryFileResponse
    {
        $headings = array_map(fn (array $column): string => $column['label'] ?? $column['name'] ?? '', $columns);

        $data = [];
        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $column) {
                $key = $column['name'] ?? $column['key'] ?? null;
                $value = $key ? data_get($row, $key) : null;
                $line[] = is_array($value) ? json_encode($value) : ($value ?? '');
            }
            $data[] = $line;
        }

        return Excel::download(new ArrayExport($headings, $data), "{$filename}.xlsx");
    }

    /**
     * Export a report as printable HTML.
     *
     * @param  string  $filename
     * @param  string  $title
     * @param  array<int, array{name: string, label: string}>  $columns
     * @param  array<int, array<string, mixed>>  $rows
     * @param  string|null  $period
     */
    public function printable(string $filename, string $title, array $columns, array $rows, ?string $period = null): Response
    {
        return ResponseFacade::view('reports.pdf.default', [
            'title' => $title,
            'columns' => $columns,
            'rows' => $rows,
            'period' => $period,
        ]);
    }
}
