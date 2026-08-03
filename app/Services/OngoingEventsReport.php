<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Product;
use App\Models\Voucher;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OngoingEventsReport
{
    public function __construct(private EventClosureMetrics $closureMetrics) {}

    /**
     * @return array{
     *     total_coupons: int,
     *     leftover_coupons: int,
     *     total_value: float,
     *     assigned_value: float,
     *     used_value: float,
     *     leftover_value: float,
     *     products: Collection<int, array{product_id: int|null, name: string, image_url: string|null, used_count: int}>
     * }
     */
    public function forEvent(Event $event): array
    {
        $metrics = $this->closureMetrics->forEvent($event);

        return [
            'total_coupons' => $metrics['counts']['total_vouchers'],
            'leftover_coupons' => $metrics['counts']['unassigned'],
            'total_value' => $metrics['values']['total_budget'],
            'assigned_value' => $metrics['values']['distributed_value'],
            'used_value' => $metrics['values']['consumed_value'],
            'leftover_value' => $metrics['values']['undistributed_value'],
            'products' => $this->productUsage($event),
        ];
    }

    /**
     * Redeemed (scanned) coupon counts per product for the event.
     *
     * @return Collection<int, array{product_id: int|null, name: string, image_url: string|null, used_count: int}>
     */
    public function productUsage(Event $event): Collection
    {
        $usedByProduct = $event->vouchers()
            ->where('status', Voucher::STATUS_REDEEMED)
            ->get(['product_id'])
            ->countBy(fn (Voucher $voucher): string => (string) ($voucher->product_id ?? 'null'));

        $products = $event->products()
            ->orderBy('id')
            ->get()
            ->map(fn (Product $product): array => [
                'product_id' => $product->id,
                'name' => $product->name,
                'image_url' => $product->imageUrl(),
                'used_count' => (int) ($usedByProduct[(string) $product->id] ?? 0),
            ]);

        if ($event->vouchers()->whereNull('product_id')->exists()) {
            $products->push([
                'product_id' => null,
                'name' => 'General pool',
                'image_url' => null,
                'used_count' => (int) ($usedByProduct['null'] ?? 0),
            ]);
        }

        return $products->values();
    }

    /**
     * @return Collection<int, array{voucher_id: string, name: string, phone: string, value: float}>
     */
    public function statementRows(Event $event): Collection
    {
        return $this->closureMetrics->forEvent($event)['vouchers']
            ->map(fn (array $row): array => [
                'voucher_id' => (string) $row['voucher_id'],
                'name' => (string) ($row['contact_name'] ?? ''),
                'phone' => (string) ($row['contact_phone'] ?? ''),
                'value' => (float) $row['card_value'],
            ])
            ->values();
    }

    public function statementCsv(Event $event): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            return '';
        }

        fputcsv($handle, ['رقم القسيمة', 'الاسم', 'الجوال', 'القيمة']);

        foreach ($this->statementRows($event) as $row) {
            fputcsv($handle, [
                $row['voucher_id'],
                $row['name'],
                $row['phone'],
                number_format($row['value'], 2, '.', ''),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $csv;
    }

    public function statementFilename(Event $event): string
    {
        $slug = Str::slug($event->name) ?: 'event';

        return "{$slug}-statement-".now()->format('Y-m-d-His').'.csv';
    }
}
