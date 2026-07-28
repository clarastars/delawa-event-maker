<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Voucher;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\DB;

class VoucherImporter
{
    /**
     * @param  array<int, array<string, string>>  $rows
     * @return array{imported: int, updated: int, skipped: int}
     */
    public function importMany(array $rows, ?Event $event = null, ?int $productId = null): array
    {
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $event, $productId, &$imported, &$updated, &$skipped): void {
            foreach ($rows as $row) {
                $payload = $this->mapRow($row);

                if ($payload !== null && $event !== null) {
                    $payload['event_id'] = $event->id;
                }

                if ($payload !== null && $productId !== null) {
                    $payload['product_id'] = $productId;
                }

                if ($payload === null) {
                    $skipped++;

                    continue;
                }

                $voucher = Voucher::query()->where('voucher_id', $payload['voucher_id'])->first();

                if ($voucher) {
                    $voucher->update($payload);
                    $updated++;

                    continue;
                }

                Voucher::create($payload);
                $imported++;
            }
        });

        return compact('imported', 'updated', 'skipped');
    }

    /**
     * @param  array<string, string>  $row
     * @return array<string, mixed>|null
     */
    private function mapRow(array $row): ?array
    {
        $voucherId = $this->value($row, ['entryid', 'entry_id', 'voucher_id', 'voucherid']);

        if ($voucherId === '') {
            return null;
        }

        $creationDate = $this->parseDate($this->value($row, ['activefrom', 'active_from', 'creation_date', 'created_at']));

        if ($creationDate === null) {
            return null;
        }

        return [
            'voucher_id' => $voucherId,
            'balance' => (float) ($this->value($row, ['balance']) ?: 0),
            'creation_date' => $creationDate,
            'expiry_date' => $this->parseDate($this->value($row, ['expirydate', 'expiry_date', 'expires_at'])),
            'status' => $this->mapStatus($this->value($row, ['status'])),
            'one_time_redemption' => $this->mapOneTimeRedemption($this->value($row, ['onetimeredemption', 'one_time_redemption'])),
        ];
    }

    /**
     * @param  array<string, string>  $row
     * @param  array<int, string>  $keys
     */
    private function value(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && trim($row[$key]) !== '') {
                return trim($row[$key]);
            }
        }

        return '';
    }

    private function mapStatus(string $status): string
    {
        return match ($status) {
            '2' => Voucher::STATUS_ACTIVE,
            '3' => Voucher::STATUS_REDEEMED,
            '4' => Voucher::STATUS_EXPIRED,
            '0', '1' => Voucher::STATUS_INACTIVE,
            'active' => Voucher::STATUS_ACTIVE,
            'inactive' => Voucher::STATUS_INACTIVE,
            'redeemed' => Voucher::STATUS_REDEEMED,
            'expired' => Voucher::STATUS_EXPIRED,
            default => Voucher::STATUS_INACTIVE,
        };
    }

    private function mapOneTimeRedemption(string $value): bool
    {
        return in_array(strtolower($value), ['1', 'true', 'yes'], true);
    }

    private function parseDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (InvalidFormatException) {
            return null;
        }
    }

    /**
     * @return array<int, string>
     */
    public static function expectedHeaders(): array
    {
        return [
            'entryId',
            'balance',
            'currencyCode',
            'ActiveFrom',
            'ExpiryDate',
            'Status',
            'OneTimeRedemption',
        ];
    }

    public static function sampleCsvContent(): string
    {
        return implode("\n", [
            implode("\t", self::expectedHeaders()),
            implode("\t", ['101229318', '400', 'SAR', '5/15/26', '6/1/2026', '2', '0']),
        ]);
    }
}
