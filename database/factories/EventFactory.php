<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => Str::lower(Str::random(8)),
            'banner_path' => null,
            'closed_at' => null,
            'closed_by_user_id' => null,
            'closure_observations' => null,
            'closure_lessons_learned' => null,
            'closure_recommendations' => null,
            'closure_pdf_path' => null,
            'closure_register_path' => null,
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'closed_at' => now(),
        ]);
    }
}
