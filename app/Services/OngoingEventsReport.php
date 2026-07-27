<?php

namespace App\Services;

use App\Models\Event;
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
     *     leftover_value: float
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
        ];
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
