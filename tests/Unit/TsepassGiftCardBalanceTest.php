<?php

use App\Services\Tsepass\GiftCardBalance;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

test('remaining balance returns net card value from api', function () {
    config([
        'services.tsepass.api_url' => 'https://api.tsepass.test',
        'services.tsepass.api_key' => 'secret',
    ]);

    Http::fake([
        'https://api.tsepass.test/queries/gift-card-simple*' => Http::response([
            'success' => true,
            'data' => [
                ['originalAmount' => 400, 'netCardValue' => 400],
            ],
        ]),
    ]);

    $balance = app(GiftCardBalance::class)->remainingBalance('EG-SA-100');

    expect($balance)->toBe(400.0);
});

test('remaining balance returns null when api is not configured', function () {
    config([
        'services.tsepass.api_url' => null,
        'services.tsepass.api_key' => null,
    ]);

    Http::fake();

    $balance = app(GiftCardBalance::class)->remainingBalance('EG-SA-100');

    expect($balance)->toBeNull();
    Http::assertNothingSent();
});

test('remaining balance includes legal_entity in request query', function () {
    config([
        'services.tsepass.api_url' => 'https://api.tsepass.test',
        'services.tsepass.api_key' => 'secret',
        'services.tsepass.legal_entity' => 'adv_test',
    ]);

    Http::fake([
        'https://api.tsepass.test/queries/gift-card-simple*' => Http::response([
            'success' => true,
            'data' => [
                ['originalAmount' => 400, 'netCardValue' => 400],
            ],
        ]),
    ]);

    app(GiftCardBalance::class)->remainingBalance('EG-SA-100');

    Http::assertSent(function (Request $request) {
        return $request['cardNumber'] === 'EG-SA-100' &&
            $request['legal_entity'] === 'adv_test';
    });
});

test('remaining balance returns null when api request fails', function () {
    config([
        'services.tsepass.api_url' => 'https://api.tsepass.test',
        'services.tsepass.api_key' => 'secret',
    ]);

    Http::fake([
        'https://api.tsepass.test/queries/gift-card-simple*' => Http::response([], 500),
    ]);

    $balance = app(GiftCardBalance::class)->remainingBalance('EG-SA-100');

    expect($balance)->toBeNull();
});
