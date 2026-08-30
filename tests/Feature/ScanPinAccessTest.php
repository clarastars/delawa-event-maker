<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the pin page to guests', function () {
    $this->get(route('admin.scan.pin'))
        ->assertOk()
        ->assertSee('Scanner Access')
        ->assertSee('Enter the scanner PIN');
});

it('redirects guests from the scan page to the pin form', function () {
    $this->get(route('admin.scan.index'))
        ->assertRedirect(route('admin.scan.pin'));
});

it('unlocks the scanner with the correct pin', function () {
    config(['scanner.pin' => '0000']);

    $this->post(route('admin.scan.pin.store'), ['pin' => '0000'])
        ->assertRedirect(route('admin.scan.index'));

    $this->get(route('admin.scan.index'))
        ->assertOk()
        ->assertSee('Scan QR Code');
});

it('rejects an incorrect pin', function () {
    config(['scanner.pin' => '0000']);

    $this->from(route('admin.scan.pin'))
        ->post(route('admin.scan.pin.store'), ['pin' => '9999'])
        ->assertRedirect(route('admin.scan.pin'))
        ->assertSessionHasErrors('pin');

    $this->get(route('admin.scan.index'))
        ->assertRedirect(route('admin.scan.pin'));
});

it('locks the scanner and forgets the pin session', function () {
    $this->withSession(['scanner_pin_verified' => true])
        ->post(route('admin.scan.pin.lock'))
        ->assertRedirect(route('admin.scan.pin'));

    $this->get(route('admin.scan.index'))
        ->assertRedirect(route('admin.scan.pin'));
});
