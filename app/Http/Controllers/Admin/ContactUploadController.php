<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactUploadRequest;
use App\Http\Requests\StoreContactRequest;
use App\Models\Event;
use App\Services\ContactImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ContactUploadController extends Controller
{
    public function __construct(private ContactImporter $contactImporter) {}

    public function create(): View
    {
        return view('admin.contacts.upload', [
            'sampleCsv' => ContactImporter::sampleCsvContent(),
            'expectedHeaders' => ContactImporter::expectedHeaders(),
            'events' => Event::query()->orderBy('name')->get(),
        ]);
    }

    public function sample(): Response
    {
        return response(ContactImporter::sampleCsvContent(), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="contacts-sample.csv"',
        ]);
    }

    public function storeForm(StoreContactRequest $request): RedirectResponse
    {
        $event = $this->resolveEvent($request->validated('event_id'));
        $entries = $request->validated('entries') ? (int) $request->validated('entries') : 1;
        $autoAssign = $request->validated('assignment_type') === 'auto_assign';

        $result = $this->contactImporter->import([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->string('phone')->toString(),
        ], $event, $entries, $autoAssign);

        if ($event === null) {
            return redirect()
                ->route('admin.contacts.upload.create')
                ->with('status', 'Contact saved.');
        }

        if ($autoAssign) {
            if ($result['assigned']) {
                return redirect()
                    ->route('admin.contacts.upload.create')
                    ->with('status', "Contact saved and a {$event->name} voucher assigned successfully.");
            }

            return redirect()
                ->route('admin.contacts.upload.create')
                ->with('status', "Contact saved, but no {$event->name} voucher could be assigned (the contact may already have one, or the pool is empty).");
        }

        return redirect()
            ->route('admin.contacts.upload.create')
            ->with('status', "Contact saved and assigned {$entries} entries for {$event->name}. They must select their product manually.");
    }

    public function store(ContactUploadRequest $request): RedirectResponse
    {
        $path = $request->file('contacts')->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return back()->withErrors(['contacts' => 'The uploaded file could not be read.']);
        }

        $rows = [];

        $headers = null;

        while (($row = fgetcsv($handle)) !== false) {
            if ($this->isBlankRow($row)) {
                continue;
            }

            if ($headers === null && $this->looksLikeHeader($row)) {
                $headers = array_map(fn (string $header): string => strtolower(trim($header)), $row);

                continue;
            }

            $contactData = $this->contactData($row, $headers);

            if ($contactData['phone'] === '') {
                continue;
            }

            $rows[] = $contactData;
        }

        fclose($handle);

        $event = $this->resolveEvent($request->validated('event_id'));
        $entries = $request->validated('entries') ? (int) $request->validated('entries') : 1;
        $autoAssign = $request->validated('assignment_type') === 'auto_assign';

        $summary = $this->contactImporter->importMany($rows, $event, $entries, $autoAssign);

        if ($event !== null) {
            $status = $autoAssign
                ? "Imported {$summary['imported']} contacts and assigned {$summary['assigned']} {$event->name} vouchers. Skipped {$summary['skipped']} rows."
                : "Imported {$summary['imported']} contacts and assigned {$entries} entries for {$event->name}. Skipped {$summary['skipped']} rows.";
        } else {
            $status = "Imported {$summary['imported']} contacts. Skipped {$summary['skipped']} rows.";
        }

        return redirect()
            ->route('admin.contacts.index')
            ->with('status', $status);
    }

    private function resolveEvent(mixed $eventId): ?Event
    {
        return filled($eventId) ? Event::query()->find((int) $eventId) : null;
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function isBlankRow(array $row): bool
    {
        return collect($row)->filter(fn (?string $value): bool => filled($value))->isEmpty();
    }

    /**
     * @param  array<int, string|null>  $row
     */
    private function looksLikeHeader(array $row): bool
    {
        $headers = array_map(fn (?string $value): string => strtolower(trim((string) $value)), $row);

        return in_array('phone', $headers, true) || in_array('mobile', $headers, true);
    }

    /**
     * @param  array<int, string|null>  $row
     * @param  array<int, string>|null  $headers
     * @return array{name: ?string, email: ?string, phone: string}
     */
    private function contactData(array $row, ?array $headers): array
    {
        $name = $this->rowValue($row, $headers, 'name', 0);
        $email = $this->rowValue($row, $headers, 'email', 1);
        $phone = $this->rowValue($row, $headers, 'phone', 2)
            ?: $this->rowValue($row, $headers, 'mobile', 2);

        return [
            'name' => $name !== '' ? $name : null,
            'email' => $email !== '' ? $email : null,
            'phone' => $phone,
        ];
    }

    /**
     * @param  array<int, string|null>  $row
     * @param  array<int, string>|null  $headers
     */
    private function rowValue(array $row, ?array $headers, string $header, int $fallbackIndex): string
    {
        if ($headers !== null) {
            $index = array_search($header, $headers, true);

            if ($index !== false) {
                return trim((string) ($row[$index] ?? ''));
            }
        }

        return trim((string) ($row[$fallbackIndex] ?? ''));
    }
}
