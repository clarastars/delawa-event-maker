<?php

use App\Models\Voucher;

test('voucher balance utilization status is full when remaining equals original balance', function () {
    $voucher = new Voucher([
        'balance' => 34,
        'remaining_balance' => 34,
    ]);

    expect($voucher->balanceUtilizationStatus())->toBe('full');
});

test('voucher balance utilization status is partial when remaining is less than original balance', function () {
    $voucher = new Voucher([
        'balance' => 34,
        'remaining_balance' => 3,
    ]);

    expect($voucher->balanceUtilizationStatus())->toBe('partial');
});

test('voucher balance utilization status is depleted when remaining balance is zero', function () {
    $voucher = new Voucher([
        'balance' => 34,
        'remaining_balance' => 0,
    ]);

    expect($voucher->balanceUtilizationStatus())->toBe('depleted');
});

test('voucher balance utilization status is null when remaining balance is not synced', function () {
    $voucher = new Voucher([
        'balance' => 34,
        'remaining_balance' => null,
    ]);

    expect($voucher->balanceUtilizationStatus())->toBeNull();
});
