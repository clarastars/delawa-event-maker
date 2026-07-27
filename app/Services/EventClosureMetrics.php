<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Voucher;
use Illuminate\Support\Collection;

class EventClosureMetrics
{
    public const UTILIZATION_UNASSIGNED = 'unassigned';

    public const UTILIZATION_ASSIGNED_PENDING = 'assigned_pending_activation';

    public const UTILIZATION_ACTIVATED_UNUSED = 'activated_unused';

    public const UTILIZATION_ACTIVATED_UNTRACKED = 'activated_untracked';

    public const UTILIZATION_PARTIALLY_USED = 'partially_used';

    public const UTILIZATION_FULLY_USED = 'fully_used';

    public const UTILIZATION_EXPIRED = 'expired';

    public const UTILIZATION_INACTIVE = 'inactive';

    /**
     * @return array{
     *     counts: array<string, int>,
     *     values: array<string, float>,
     *     rates: array<string, float|null>,
     *     vouchers: Collection<int, array<string, mixed>>
     * }
     */
    public function forEvent(Event $event): array
    {
        $vouchers = $event->vouchers()
            ->with('contact')
            ->orderBy('voucher_id')
            ->get();

        $rows = $vouchers->map(fn (Voucher $voucher): array => $this->voucherRow($voucher));

        $counts = [
            'total_vouchers' => $vouchers->count(),
            'unassigned' => $rows->where('utilization', self::UTILIZATION_UNASSIGNED)->count(),
            'assigned_pending_activation' => $rows->where('utilization', self::UTILIZATION_ASSIGNED_PENDING)->count(),
            'activated_unused' => $rows->where('utilization', self::UTILIZATION_ACTIVATED_UNUSED)->count(),
            'activated_untracked' => $rows->where('utilization', self::UTILIZATION_ACTIVATED_UNTRACKED)->count(),
            'partially_used' => $rows->where('utilization', self::UTILIZATION_PARTIALLY_USED)->count(),
            'fully_used' => $rows->where('utilization', self::UTILIZATION_FULLY_USED)->count(),
            'expired' => $rows->where('utilization', self::UTILIZATION_EXPIRED)->count(),
            'inactive' => $rows->where('utilization', self::UTILIZATION_INACTIVE)->count(),
        ];

        $totalBudget = (float) $vouchers->sum('balance');
        $distributedValue = (float) $rows->where('is_assigned', true)->sum('card_value');
        $activatedValue = (float) $rows->where('is_activated', true)->sum('card_value');
        $consumedValue = (float) $rows->sum('value_consumed');
        $remainingValue = (float) $rows->whereNotNull('remaining_balance')->sum('remaining_balance');
        $untrackedValue = (float) $rows
            ->where('is_activated', true)
            ->whereNull('remaining_balance')
            ->sum('card_value');

        $values = [
            'total_budget' => $totalBudget,
            'distributed_value' => $distributedValue,
            'activated_value' => $activatedValue,
            'consumed_value' => $consumedValue,
            'remaining_value' => $remainingValue,
            'untracked_value' => $untrackedValue,
            'undistributed_value' => max(0, $totalBudget - $distributedValue),
        ];

        $rates = [
            'assignment_rate' => $this->percentage($counts['total_vouchers'] - $counts['unassigned'], $counts['total_vouchers']),
            'activation_rate' => $this->percentage(
                $counts['activated_unused'] + $counts['activated_untracked'] + $counts['partially_used'] + $counts['fully_used'],
                $counts['total_vouchers']
            ),
            'utilization_rate' => $activatedValue > 0
                ? $this->percentage($consumedValue, $activatedValue)
                : null,
        ];

        return [
            'counts' => $counts,
            'values' => $values,
            'rates' => $rates,
            'vouchers' => $rows,
        ];
    }

    public function utilizationLabel(string $utilization): string
    {
        return match ($utilization) {
            self::UTILIZATION_UNASSIGNED => 'Unassigned',
            self::UTILIZATION_ASSIGNED_PENDING => 'Assigned, not activated',
            self::UTILIZATION_ACTIVATED_UNUSED => 'Activated, balance intact',
            self::UTILIZATION_ACTIVATED_UNTRACKED => 'Activated, balance not synced',
            self::UTILIZATION_PARTIALLY_USED => 'Partially spent',
            self::UTILIZATION_FULLY_USED => 'Fully spent',
            self::UTILIZATION_EXPIRED => 'Expired',
            self::UTILIZATION_INACTIVE => 'Inactive',
            default => ucfirst(str_replace('_', ' ', $utilization)),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function voucherRow(Voucher $voucher): array
    {
        $utilization = $this->utilizationStatus($voucher);
        $cardValue = (float) $voucher->balance;
        $remainingBalance = $voucher->remaining_balance !== null ? (float) $voucher->remaining_balance : null;
        $valueConsumed = $remainingBalance !== null ? max(0, $cardValue - $remainingBalance) : 0.0;
        $isAssigned = $voucher->contact_id !== null;
        $isActivated = $isAssigned && ($voucher->contact?->isActivated() ?? false);

        return [
            'voucher_id' => $voucher->voucher_id,
            'status' => $voucher->status,
            'utilization' => $utilization,
            'utilization_label' => $this->utilizationLabel($utilization),
            'contact_name' => $voucher->contact?->name,
            'contact_phone' => $voucher->contact?->phone,
            'contact_activated_at' => $voucher->contact?->activated_at?->format('Y-m-d H:i'),
            'card_value' => $cardValue,
            'remaining_balance' => $remainingBalance,
            'value_consumed' => $valueConsumed,
            'is_assigned' => $isAssigned,
            'is_activated' => $isActivated,
            'expiry_date' => $voucher->expiry_date?->format('Y-m-d'),
            'redeemed_at' => $voucher->redeemed_at?->format('Y-m-d H:i'),
        ];
    }

    private function utilizationStatus(Voucher $voucher): string
    {
        if ($voucher->status === Voucher::STATUS_EXPIRED) {
            return self::UTILIZATION_EXPIRED;
        }

        if ($voucher->status === Voucher::STATUS_INACTIVE) {
            return self::UTILIZATION_INACTIVE;
        }

        if ($voucher->contact_id === null) {
            return self::UTILIZATION_UNASSIGNED;
        }

        if ($voucher->status === Voucher::STATUS_REDEEMED) {
            return self::UTILIZATION_FULLY_USED;
        }

        $contactActivated = $voucher->contact?->isActivated() ?? false;

        if (! $contactActivated) {
            return self::UTILIZATION_ASSIGNED_PENDING;
        }

        if ($voucher->remaining_balance !== null) {
            if ((float) $voucher->remaining_balance <= 0) {
                return self::UTILIZATION_FULLY_USED;
            }

            if ((float) $voucher->remaining_balance < (float) $voucher->balance) {
                return self::UTILIZATION_PARTIALLY_USED;
            }

            return self::UTILIZATION_ACTIVATED_UNUSED;
        }

        return self::UTILIZATION_ACTIVATED_UNTRACKED;
    }

    private function percentage(float|int $part, float|int $total): ?float
    {
        if ($total <= 0) {
            return null;
        }

        return round(((float) $part / (float) $total) * 100, 1);
    }
}
