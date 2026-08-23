<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Event;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ContactImporter
{
    /**
     * @return array<int, string>
     */
    public static function expectedHeaders(): array
    {
        return ['name', 'email', 'phone'];
    }

    public static function sampleCsvContent(): string
    {
        return implode("\n", [
            implode(',', self::expectedHeaders()),
            implode(',', ['Sara Ahmed', 'sara@example.com', '+966551234567']),
            implode(',', ['Omar Ali', '', '0559876543']),
        ]);
    }

    /**
     * @param  array{name: ?string, email: ?string, phone: string}  $data
     * @return array{contact: Contact, assigned: bool, skipped: bool}
     */
    public function import(array $data, ?Event $event = null, int $entries = 1, bool $autoAssign = false): array
    {
        $phone = trim($data['phone']);
        $phoneNormalized = Contact::normalizePhone($phone);

        if ($phoneNormalized === '') {
            throw new \InvalidArgumentException('A valid phone number is required.');
        }

        return DB::transaction(function () use ($data, $phone, $phoneNormalized, $event, $entries, $autoAssign): array {
            $email = filled($data['email'] ?? null) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)
                ? $data['email']
                : null;

            $contact = Contact::updateOrCreate(
                ['phone_normalized' => $phoneNormalized],
                [
                    'name' => filled($data['name'] ?? null) ? trim((string) $data['name']) : null,
                    'email' => $email,
                    'phone' => $phone,
                    'phone_normalized' => $phoneNormalized,
                ]
            );

            if ($event !== null) {
                if ($autoAssign) {
                    $assignedCount = $this->claimEventVouchers($contact, $event, max(1, $entries));

                    return [
                        'contact' => $contact,
                        'assigned' => $assignedCount > 0,
                        'skipped' => $assignedCount === 0,
                    ];
                } else {
                    $contact->events()->syncWithoutDetaching([$event->id => ['entries' => $entries]]);
                }
            }

            return [
                'contact' => $contact,
                'assigned' => false,
                'skipped' => false,
            ];
        });
    }

    /**
     * Assign an available active voucher to the contact. Contacts may hold
     * multiple vouchers; pass a voucher id or event to narrow the pool.
     *
     * @return array{contact: Contact, assigned: bool, voucher: ?Voucher}
     */
    public function assignVoucher(Contact $contact, ?int $voucherId = null, ?Event $event = null): array
    {
        return DB::transaction(function () use ($contact, $voucherId, $event): array {
            $voucherQuery = Voucher::query()
                ->whereNull('contact_id')
                ->where('status', Voucher::STATUS_ACTIVE)
                ->where(function (Builder $query): void {
                    $query
                        ->whereNull('event_id')
                        ->orWhereHas('event', fn (Builder $eventQuery) => $eventQuery->open());
                })
                ->orderByRaw('product_id IS NOT NULL')
                ->orderBy('id')
                ->lockForUpdate();

            if ($voucherId !== null) {
                $voucherQuery->whereKey($voucherId);
            }

            if ($event !== null) {
                $voucherQuery->where('event_id', $event->id);
            }

            $voucher = $voucherQuery->first();

            if (! $voucher) {
                return [
                    'contact' => $contact,
                    'assigned' => false,
                    'voucher' => null,
                ];
            }

            $voucher->update(['contact_id' => $contact->id]);

            return [
                'contact' => $contact->fresh(['vouchers']),
                'assigned' => true,
                'voucher' => $voucher,
            ];
        });
    }

    /**
     * Ensure the contact holds a redeemable voucher for the event, claiming
     * one from the event's unassigned pool when needed.
     */
    public function claimEventVoucher(Contact $contact, Event $event): ?Voucher
    {
        if ($this->claimEventVouchers($contact, $event, 1) === 0) {
            return null;
        }

        return $contact->vouchers()
            ->where('event_id', $event->id)
            ->redeemable()
            ->latest('id')
            ->first();
    }

    /**
     * Ensure the contact holds at least $count redeemable vouchers for the event,
     * claiming from the event's unassigned pool as needed. Prefers general
     * (no product) vouchers via assignVoucher ordering.
     */
    public function claimEventVouchers(Contact $contact, Event $event, int $count): int
    {
        return DB::transaction(function () use ($contact, $event, $count): int {
            $existingCount = $contact->vouchers()
                ->where('event_id', $event->id)
                ->redeemable()
                ->count();

            $needed = max(0, $count - $existingCount);

            for ($i = 0; $i < $needed; $i++) {
                $voucher = $this->assignVoucher($contact, event: $event)['voucher'];

                if ($voucher === null) {
                    break;
                }
            }

            return $contact->vouchers()
                ->where('event_id', $event->id)
                ->redeemable()
                ->count();
        });
    }

    /**
     * @param  array<int, array{name: ?string, email: ?string, phone: string}>  $rows
     * @return array{imported: int, assigned: int, skipped: int}
     */
    public function importMany(array $rows, ?Event $event = null, int $entries = 1, bool $autoAssign = false): array
    {
        $imported = 0;
        $assigned = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            try {
                $result = $this->import($row, $event, $entries, $autoAssign);
            } catch (\InvalidArgumentException) {
                $skipped++;

                continue;
            }

            $imported++;

            if ($result['skipped']) {
                $skipped++;
            } elseif ($result['assigned']) {
                $assigned++;
            }
        }

        return compact('imported', 'assigned', 'skipped');
    }
}
