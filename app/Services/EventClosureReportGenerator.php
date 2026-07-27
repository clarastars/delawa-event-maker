<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class EventClosureReportGenerator
{
    public function __construct(private EventClosureMetrics $metrics) {}

    /**
     * @param  array{
     *     counts: array<string, int>,
     *     values: array<string, float>,
     *     rates: array<string, float|null>,
     *     vouchers: Collection<int, array<string, mixed>>
     * }  $metrics
     * @param  array{
     *     observations: ?string,
     *     lessons_learned: ?string,
     *     recommendations: ?string
     * }  $closureNotes
     * @return array{pdf_path: string, register_path: string}
     */
    public function generate(Event $event, User $closedBy, array $metrics, array $closureNotes): array
    {
        $directory = "event-closures/{$event->id}";
        $timestamp = now()->format('Y-m-d-His');
        $slug = Str::slug($event->name);

        $pdfPath = "{$directory}/{$slug}-executive-summary-{$timestamp}.pdf";
        $registerPath = "{$directory}/{$slug}-voucher-register-{$timestamp}.csv";

        $html = View::make('admin.reports.event-closure-pdf', [
            'event' => $event,
            'closedBy' => $closedBy,
            'metrics' => $metrics,
            'closureNotes' => $closureNotes,
            'generatedAt' => now(),
        ])->render();

        Storage::disk('local')->put($pdfPath, $this->renderPdf($html));
        Storage::disk('local')->put($registerPath, $this->registerCsv($metrics['vouchers']));

        return [
            'pdf_path' => $pdfPath,
            'register_path' => $registerPath,
        ];
    }

    private function renderPdf(string $html): string
    {
        $tempDir = storage_path('app/mpdf-tmp');

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'tempDir' => $tempDir,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 15,
            'margin_bottom' => 15,
            'default_font' => 'dejavusans',
        ]);

        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $voucherRows
     */
    private function registerCsv(Collection $voucherRows): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            return '';
        }

        fputcsv($handle, [
            'voucher_id',
            'status',
            'utilization',
            'contact_name',
            'contact_phone',
            'contact_activated_at',
            'card_value',
            'remaining_balance',
            'value_consumed',
            'expiry_date',
            'redeemed_at',
        ]);

        foreach ($voucherRows as $row) {
            fputcsv($handle, [
                $row['voucher_id'],
                $row['status'],
                $row['utilization_label'],
                $row['contact_name'] ?? '',
                $row['contact_phone'] ?? '',
                $row['contact_activated_at'] ?? '',
                number_format((float) $row['card_value'], 2, '.', ''),
                $row['remaining_balance'] !== null ? number_format((float) $row['remaining_balance'], 2, '.', '') : '',
                number_format((float) $row['value_consumed'], 2, '.', ''),
                $row['expiry_date'] ?? '',
                $row['redeemed_at'] ?? '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $csv;
    }
}
