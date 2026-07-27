<?php

use App\Support\PhoneNumber;

test('converts saudi local number to e164', function () {
    expect(PhoneNumber::toE164('0551234567'))->toBe('+966551234567');
});

test('accepts e164 phone numbers', function () {
    expect(PhoneNumber::toE164('+966551234567'))->toBe('+966551234567');
});

test('rejects invalid phone numbers', function () {
    expect(PhoneNumber::toE164('12345'))->toBeNull();
});
