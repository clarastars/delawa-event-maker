<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoucherUploadRequest;
use App\Models\Event;
use App\Services\VoucherImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class VoucherUploadController extends Controller
{
    public function __construct(private VoucherImporter $voucherImporter) {}

    public function create(Request $request): View
    {
        return view('admin.vouchers.upload', [
            'sampleCsv' => VoucherImporter::sampleCsvContent(),
            'expectedHeaders' => VoucherImporter::expectedHeaders(),
            'events' => Event::query()->with('products')->orderBy('name')->get(),
            'selectedEventId' => $request->integer('event') ?: null,
        ]);
    }

    public function sample(): Response
    {
        return response(VoucherImporter::sampleCsvContent(), 200, [
            'Content-Type' => 'text/tab-separated-values',
            'Content-Disposition' => 'attachment; filename="vouchers-sample.tsv"',
        ]);
    }

    public function store(VoucherUploadRequest $request): RedirectResponse
    {
        $path = $request->file('vouchers')->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->withErrors(['vouchers' => 'The uploaded file could not be read.']);
        }

        $rows = $this->parseFile($handle);
        fclose($handle);

        if ($rows === []) {
            return back()->withErrors(['vouchers' => 'No voucher rows were found in the file.']);
        }

        $event = Event::query()->findOrFail($request->validated('event_id'));
        $productId = $request->validated('product_id') ? (int) $request->validated('product_id') : null;

        $summary = $this->voucherImporter->importMany($rows, $event, $productId);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('status', "Imported {$summary['imported']} vouchers, updated {$summary['updated']}, skipped {$summary['skipped']} rows for {$event->name}.");
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parseFile(mixed $handle): array
    {
        $firstLine = fgets($handle);

        if ($firstLine === false) {
            return [];
        }

        $delimiter = substr_count($firstLine, "\t") >= substr_count($firstLine, ',') ? "\t" : ',';
        $headers = $this->normalizeHeaders(str_getcsv(rtrim($firstLine), $delimiter));
        $rows = [];

        while (($line = fgets($handle)) !== false) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $values = str_getcsv($line, $delimiter);

            if ($this->isBlankRow($values)) {
                continue;
            }

            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = trim((string) ($values[$index] ?? ''));
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param  array<int, string|null>  $headers
     * @return array<int, string>
     */
    private function normalizeHeaders(array $headers): array
    {
        return array_map(
            fn (?string $header): string => strtolower(trim((string) $header)),
            $headers
        );
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function isBlankRow(array $row): bool
    {
        return collect($row)->filter(fn (?string $value): bool => filled($value))->isEmpty();
    }
}
