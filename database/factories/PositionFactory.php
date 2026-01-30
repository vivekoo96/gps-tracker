<?php

namespace Database\Factories;

use App\Models\Position;
use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Position>
 */
class PositionFactory extends Factory
{
    protected $model = Position::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_id' => 1, // Will be overridden in tests
            'latitude' => fake()->latitude(25, 35),
            'longitude' => fake()->longitude(68, 97),
            'altitude' => fake()->numberBetween(0, 1000),
            'speed' => fake()->numberBetween(0, 120),
            'course' => fake()->numberBetween(0, 359), // Changed from 'heading' to 'course'
            'satellites' => fake()->numberBetween(4, 15),
            'fix_time' => now()->subHours(fake()->numberBetween(0, 24)),
            'ignition' => fake()->boolean(),
            'attributes' => null,
            'raw' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a position at specific coordinates.
     */
    public function at(float $latitude, float $longitude): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }

    /**
     * Create a position with specific speed.
     */
    public function withSpeed(int $speed): static
    {
        return $this->state(fn (array $attributes) => [
            'speed' => $speed,
        ]);
    }

    /**
     * Create a stationary position (speed = 0).
     */
    public function stationary(): static
    {
        return $this->state(fn (array $attributes) => [
            'speed' => 0,
        ]);
    }
}
