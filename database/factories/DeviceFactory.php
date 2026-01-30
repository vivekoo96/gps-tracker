<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    protected $model = Device::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true) . ' GPS',
            'imei' => fake()->unique()->numerify('###############'), // 15 digits
            'model' => fake()->randomElement(['GT06', 'TK103', 'TK102', 'H02', 'GT800', 'MT100']),
            'sim_number' => fake()->optional()->numerify('##########'),
            'status' => 'active',
            'last_seen_at' => now(),
            'meta' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the device is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
