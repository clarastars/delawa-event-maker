<?php

use App\Services\Otp\DebugOtp;
use Tests\TestCase;

uses(TestCase::class);

test('debug otp send is a no-op', function () {
    (new DebugOtp)->send('+966551234567');

    expect(true)->toBeTrue();
});

test('debug otp verify accepts configured code', function () {
    config(['services.authentica.debug_otp_code' => '1234']);

    $otp = new DebugOtp;

    expect($otp->verify('+966551234567', '1234'))->toBeTrue();
    expect($otp->verify('+966551234567', '9999'))->toBeFalse();
});

test('debug otp verify strips non-digits', function () {
    config(['services.authentica.debug_otp_code' => '1234']);

    expect((new DebugOtp)->verify('+966551234567', '12-34'))->toBeTrue();
});
