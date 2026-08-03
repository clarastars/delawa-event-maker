<?php

use App\Models\Event;
use App\Models\Product;
use App\Models\Voucher;
use App\Services\LocalVoucherGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('generator creates unique local vouchers with balance and remaining balance', function () {
    $event = Event::factory()->create();
    $product = Product::factory()->create(['event_id' => $event->id]);

    $summary = app(LocalVoucherGenerator::class)->generate(
        event: $event,
        quantity: 5,
        balance: 25.0,
        product: $product,
        expiryDate: now()->addMonth()->toDateString(),
        oneTimeRedemption: true,
    );

    expect($summary['created'])->toBe(5)
        ->and($summary['vouchers'])->toHaveCount(5);

    $vouchers = Voucher::query()->where('event_id', $event->id)->get();

    expect($vouchers)->toHaveCount(5)
        ->and($vouchers->pluck('voucher_id')->unique())->toHaveCount(5);

    foreach ($vouchers as $voucher) {
        expect($voucher)
            ->source->toBe(Voucher::SOURCE_LOCAL)
            ->product_id->toBe($product->id)
            ->balance->toEqual(25.0)
            ->remaining_balance->toEqual(25.0)
            ->status->toBe(Voucher::STATUS_ACTIVE)
            ->one_time_redemption->toBeTrue()
            ->and($voucher->voucher_id)->toStartWith('DLW');
    }
});

test('generator rejects product from another event', function () {
    $event = Event::factory()->create();
    $otherEvent = Event::factory()->create();
    $product = Product::factory()->create(['event_id' => $otherEvent->id]);

    app(LocalVoucherGenerator::class)->generate(
        event: $event,
        quantity: 1,
        balance: 18.0,
        product: $product,
    );
})->throws(InvalidArgumentException::class);
