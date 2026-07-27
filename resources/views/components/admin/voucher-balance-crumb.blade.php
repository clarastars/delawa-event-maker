@props([
    'voucher',
])

@php
    $status = $voucher->balanceUtilizationStatus();

    [$classes, $label, $title] = match ($status) {
        'full' => ['bg-emerald-50 text-emerald-800 ring-emerald-100', number_format((float) $voucher->remaining_balance, 2).' SR', 'Full balance'],
        'partial' => ['bg-amber-50 text-amber-800 ring-amber-100', number_format((float) $voucher->remaining_balance, 2).' SR', 'Partially used'],
        'depleted' => ['bg-red-50 text-red-700 ring-red-100', '0.00 SR', 'Fully used'],
        default => ['bg-slate-100 text-slate-600 ring-slate-200', null, 'Balance not synced'],
    };
@endphp

<span
    {{ $attributes->class("inline-flex items-center rounded-full px-3 py-1 text-xs font-bold ring-1 {$classes}") }}
    @if ($title) title="{{ $title }}" @endif
    dir="ltr"
>
    {{ $voucher->voucher_id }}@if ($label) · {{ $label }}@endif
</span>
