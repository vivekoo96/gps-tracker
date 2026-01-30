<?php

namespace Database\Factories;

use App\Models\Geofence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Geofence>
 */
class GeofenceFactory extends Factory
{
    protected $model = Geofence::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . ' Zone',
            'description' => fake()->optional()->sentence(),
            'latitude' => fake()->latitude(25, 35), // India region
            'longitude' => fake()->longitude(68, 97),
            'radius' => fake()->numberBetween(100, 5000), // 100m to 5km
            'color' => '#' . str_pad(dechex(fake()->numberBetween(0, 16777215)), 6, '0', STR_PAD_LEFT),
            'is_active' => true,
            'created_by' => \App\Models\User::factory(), // Create user automatically
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the geofence is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a geofence with specific coordinates.
     */
    public function at(float $latitude, float $longitude, int $radius = 500): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius' => $radius,
        ]);
    }
}
