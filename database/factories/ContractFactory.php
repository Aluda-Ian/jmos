<?php

namespace Database\Factories;

use App\Models\Contract;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template' => 'photo-video-social',
            'title' => 'Photography, Videography and Social Media Services',
            'client_name' => fake()->company(),
            'client_email' => fake()->safeEmail(),
            'client_phone' => '+2547'.fake()->numerify('########'),
            'signatory_name' => fake()->name(),
            'signatory_position' => 'Director',
            'fields' => ['services' => ['photography_project'], 'fee' => 150000, 'deposit_percent' => 50],
            'body' => '<h2>1. INTERPRETATION</h2><p>Sample agreement text.</p>',
            'status' => Contract::STATUS_DRAFT,
            'created_by' => 'Barny Kiome',
        ];
    }

    public function sent(): static
    {
        return $this->state(fn () => [
            'status' => Contract::STATUS_SENT,
            'sent_at' => now(),
            'provider_signed_at' => now(),
        ]);
    }
}
