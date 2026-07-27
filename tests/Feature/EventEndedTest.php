<?php

test('the application shows the event ended page', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('انتهى الحدث')
        ->assertSee('English');
});

test('root redirect from legacy accept path shows event ended page', function () {
    $this->get('/accept')
        ->assertRedirect('/');
});

test('legacy invite routes are disabled', function () {
    $this->get('/accept/otp/send')->assertRedirect('/');
    $this->post('/accept/otp/send')->assertRedirect('/');
});

test('admin panel is accessible again', function () {
    $this->get('/admin/login')->assertSuccessful();
    $this->get('/admin/vouchers')->assertRedirect(route('admin.login'));
});

test('event ended page supports english locale', function () {
    $this->get('/?lang=en')
        ->assertSuccessful()
        ->assertSee('This event has ended')
        ->assertSee('العربية');
});
