<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::query()->updateOrCreate(
            ['email' => 'it@adv-line.com'],
            [
                'name' => 'IT Delawa KSA',
                'password' => '!d@83XoLt00u',
                'email_verified_at' => now(),
            ],
        );
    }
}
