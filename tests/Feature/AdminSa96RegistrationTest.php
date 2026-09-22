<?php

use App\Models\Sa96Registration;
use App\Models\User;
use App\Support\Sa96Privacy;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected to login for sa96 admin page', function () {
    $this->get(route('admin.sa96.index'))->assertRedirect(route('admin.login'));
});

test('scanners cannot view sa96 registrations', function () {
    $scanner = User::factory()->create(['role' => User::ROLE_SCANNER]);

    $this->actingAs($scanner)
        ->get(route('admin.sa96.index'))
        ->assertForbidden();
});

test('admin can view active sa96 registrations', function () {
    $admin = User::factory()->create();

    Sa96Registration::factory()->create([
        'name' => 'Sara Ahmed',
        'phone' => '+966551234567',
        'phone_hash' => Sa96Privacy::phoneHash('+966551234567'),
        'birth_year' => 1995,
    ]);

    Sa96Registration::factory()->withdrawn()->create([
        'phone_hash' => Sa96Privacy::phoneHash('+966559999999'),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.sa96.index'))
        ->assertSuccessful()
        ->assertSee('National Day 96', false)
        ->assertSee('Sara Ahmed', false)
        ->assertSee('+966551234567', false)
        ->assertSee('1995', false)
        ->assertSee('1 active', false)
        ->assertSee('Active', false);
});

test('admin can filter withdrawn sa96 registrations', function () {
    $admin = User::factory()->create();

    Sa96Registration::factory()->create([
        'name' => 'Active Visitor',
        'phone' => '+966551111111',
        'phone_hash' => Sa96Privacy::phoneHash('+966551111111'),
    ]);

    Sa96Registration::factory()->withdrawn()->create([
        'phone_hash' => Sa96Privacy::phoneHash('+966552222222'),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.sa96.index', ['status' => 'withdrawn']))
        ->assertSuccessful()
        ->assertSee('1 withdrawn', false)
        ->assertDontSee('Active Visitor', false);
});

test('admin can search sa96 registrations by phone', function () {
    $admin = User::factory()->create();

    Sa96Registration::factory()->create([
        'name' => 'Sara',
        'phone' => '+966551234567',
        'phone_hash' => Sa96Privacy::phoneHash('+966551234567'),
    ]);

    Sa96Registration::factory()->create([
        'name' => 'Other',
        'phone' => '+966559876543',
        'phone_hash' => Sa96Privacy::phoneHash('+966559876543'),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.sa96.index', ['search' => '0551234567']))
        ->assertSuccessful()
        ->assertSee('Sara', false)
        ->assertDontSee('Other', false);
});

test('admin can export sa96 registrations as csv', function () {
    $admin = User::factory()->create();

    Sa96Registration::factory()->create([
        'name' => 'Sara Ahmed',
        'phone' => '+966551234567',
        'phone_hash' => Sa96Privacy::phoneHash('+966551234567'),
        'birth_year' => 1995,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.sa96.export'));

    $response->assertSuccessful();

    $csv = $response->streamedContent();

    expect($csv)
        ->toContain('name,phone,birth_year')
        ->toContain('Sara Ahmed')
        ->toContain('+966551234567')
        ->toContain('1995');
});
