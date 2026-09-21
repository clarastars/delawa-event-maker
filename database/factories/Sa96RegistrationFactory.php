<?php

namespace Database\Factories;

use App\Models\Sa96Registration;
use App\Support\Sa96Privacy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sa96Registration>
 */
class Sa96RegistrationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phone = '+9665'.fake()->unique()->numerify('########');
        $locale = fake()->randomElement(['ar', 'en']);

        return [
            'name' => fake()->name(),
            'phone' => $phone,
            'phone_hash' => Sa96Privacy::phoneHash($phone),
            'birth_year' => fake()->numberBetween(1960, Sa96Privacy::maxBirthYear()),
            'locale' => $locale,
            'consent_privacy_notice' => true,
            'consent_campaign' => true,
            'consent_capacity' => true,
            'consent_cross_border' => true,
            'consent_marketing' => true,
            'consent_notice_version' => config('sa96.notice_version'),
            'consent_method' => 'web_form',
            'consent_snapshot' => config('sa96.consents.'.$locale),
            'consented_at' => now(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Pest',
            'withdrawn_at' => null,
        ];
    }

    public function withdrawn(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => null,
            'phone' => null,
            'birth_year' => null,
            'ip_address' => null,
            'user_agent' => null,
            'withdrawn_at' => now(),
        ]);
    }
}
