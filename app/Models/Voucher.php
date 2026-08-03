<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['contact_id', 'event_id', 'product_id', 'source', 'voucher_id', 'creation_date', 'expiry_date', 'balance', 'remaining_balance', 'remaining_balance_synced_at', 'status', 'one_time_redemption', 'redeemed_at'])]
class Voucher extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_REDEEMED = 'redeemed';

    public const STATUS_EXPIRED = 'expired';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_REDEEMED,
        self::STATUS_EXPIRED,
    ];

    public const SOURCE_TSEPASS = 'tsepass';

    public const SOURCE_LOCAL = 'local';

    public const SOURCES = [
        self::SOURCE_TSEPASS,
        self::SOURCE_LOCAL,
    ];

    protected function casts(): array
    {
        return [
            'creation_date' => 'date',
            'expiry_date' => 'date',
            'balance' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
            'remaining_balance_synced_at' => 'datetime',
            'one_time_redemption' => 'boolean',
            'redeemed_at' => 'datetime',
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isLocal(): bool
    {
        return $this->source === self::SOURCE_LOCAL;
    }

    public function isTsepass(): bool
    {
        return $this->source === self::SOURCE_TSEPASS;
    }

    /**
     * Remaining balance for display: local uses stored value; callers should use GiftCardBalance for tsepass.
     */
    public function storedRemainingBalance(): ?float
    {
        if ($this->remaining_balance !== null) {
            return (float) $this->remaining_balance;
        }

        if ($this->isLocal()) {
            return (float) $this->balance;
        }

        return null;
    }

    public function scopeRedeemable(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_ACTIVE)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', now()->toDateString());
            });
    }

    public function scopeTsepass(Builder $query): Builder
    {
        return $query->where('source', self::SOURCE_TSEPASS);
    }

    public function scopeLocal(Builder $query): Builder
    {
        return $query->where('source', self::SOURCE_LOCAL);
    }

    /**
     * @return 'full'|'partial'|'depleted'|null
     */
    public function balanceUtilizationStatus(): ?string
    {
        if ($this->remaining_balance === null) {
            return null;
        }

        $remaining = (float) $this->remaining_balance;

        if ($remaining <= 0) {
            return 'depleted';
        }

        if ($remaining < (float) $this->balance) {
            return 'partial';
        }

        return 'full';
    }
}
