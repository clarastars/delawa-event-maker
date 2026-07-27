<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'voucher',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'voucher',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $status = $voucher->balanceUtilizationStatus();

    [$classes, $label, $title] = match ($status) {
        'full' => ['bg-emerald-50 text-emerald-800 ring-emerald-100', number_format((float) $voucher->remaining_balance, 2).' SR', 'Full balance'],
        'partial' => ['bg-amber-50 text-amber-800 ring-amber-100', number_format((float) $voucher->remaining_balance, 2).' SR', 'Partially used'],
        'depleted' => ['bg-red-50 text-red-700 ring-red-100', '0.00 SR', 'Fully used'],
        default => ['bg-slate-100 text-slate-600 ring-slate-200', null, 'Balance not synced'],
    };
?>

<span
    <?php echo e($attributes->class("inline-flex items-center rounded-full px-3 py-1 text-xs font-bold ring-1 {$classes}")); ?>

    <?php if($title): ?> title="<?php echo e($title); ?>" <?php endif; ?>
    dir="ltr"
>
    <?php echo e($voucher->voucher_id); ?><?php if($label): ?> · <?php echo e($label); ?><?php endif; ?>
</span>
<?php /**PATH /Users/shafiqal-shaar/projects/delawa-event-maker/resources/views/components/admin/voucher-balance-crumb.blade.php ENDPATH**/ ?>