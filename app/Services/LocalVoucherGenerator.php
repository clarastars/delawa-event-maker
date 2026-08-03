<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class LocalVoucherGenerator
{
    private const CODE_PREFIX = 'DLW';

    private const CODE_RANDOM_LENGTH = 12;

    private const MAX_CODE_ATTEMPTS = 25;

    /**
     * @return array{created: int, vouchers: list<Voucher>}
     */
    public function generate(
        Event $event,
        int $quantity,
        float $balance,
        ?Product $product = null,
        ?string $expiryDate = null,
        bool $oneTimeRedemption = true,
    ): array {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Quantity must be at least 1.');
        }

        if ($product !== null && (int) $product->event_id !== (int) $event->id) {
            throw new InvalidArgumentException('Product does not belong to the selected event.');
        }

        $vouchers = [];

        DB::transaction(function () use ($event, $quantity, $balance, $product, $expiryDate, $oneTimeRedemption, &$vouchers): void {
            for ($i = 0; $i < $quantity; $i++) {
                $vouchers[] = Voucher::create([
                    'event_id' => $event->id,
                    'product_id' => $product?->id,
                    'source' => Voucher::SOURCE_LOCAL,
                    'voucher_id' => $this->uniqueCode(),
                    'creation_date' => now()->toDateString(),
                    'expiry_date' => $expiryDate,
                    'balance' => $balance,
                    'remaining_balance' => $balance,
                    'status' => Voucher::STATUS_ACTIVE,
                    'one_time_redemption' => $oneTimeRedemption,
                ]);
            }
        });

        return [
            'created' => count($vouchers),
            'vouchers' => $vouchers,
        ];
    }

    private function uniqueCode(): string
    {
        for ($attempt = 0; $attempt < self::MAX_CODE_ATTEMPTS; $attempt++) {
            $code = self::CODE_PREFIX.Str::upper(Str::random(self::CODE_RANDOM_LENGTH));

            if (! Voucher::query()->where('voucher_id', $code)->exists()) {
                return $code;
            }
        }

        throw new RuntimeException('Unable to generate a unique voucher code.');
    }
}
