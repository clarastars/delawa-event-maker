<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows admin to view team list', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->get(route('admin.team.index'))
        ->assertOk()
        ->assertSee('Team Management');
});

it('prevents scanner from viewing team list', function () {
    $scanner = User::factory()->create(['role' => User::ROLE_SCANNER]);

    $this->actingAs($scanner)
        ->get(route('admin.team.index'))
        ->assertForbidden();
});

it('allows admin to create a new team member', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->post(route('admin.team.store'), [
            'name' => 'New Scanner',
            'email' => 'scanner@example.com',
            'password' => 'password123',
            'role' => User::ROLE_SCANNER,
        ])
        ->assertRedirect(route('admin.team.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('users', [
        'email' => 'scanner@example.com',
        'role' => User::ROLE_SCANNER,
    ]);
});

it('allows admin to update a team member', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $teamMember = User::factory()->create(['role' => User::ROLE_SCANNER]);

    $this->actingAs($admin)
        ->put(route('admin.team.update', $teamMember), [
            'name' => 'Updated Name',
            'email' => $teamMember->email,
            'role' => User::ROLE_ADMIN,
        ])
        ->assertRedirect(route('admin.team.index'));

    $this->assertDatabaseHas('users', [
        'id' => $teamMember->id,
        'name' => 'Updated Name',
        'role' => User::ROLE_ADMIN,
    ]);
});

it('allows admin to delete a team member', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $teamMember = User::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.team.destroy', $teamMember))
        ->assertRedirect(route('admin.team.index'));

    $this->assertDatabaseMissing('users', [
        'id' => $teamMember->id,
    ]);
});

it('prevents admin from deleting themselves', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->delete(route('admin.team.destroy', $admin))
        ->assertRedirect()
        ->assertSessionHas('status', 'You cannot delete yourself.');

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});

it('redirects scanner to scan page on login', function () {
    $scanner = User::factory()->create(['role' => User::ROLE_SCANNER]);

    $this->actingAs($scanner)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.scan.index'));
});

it('redirects admin to events page on login', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.events.index'));
});
