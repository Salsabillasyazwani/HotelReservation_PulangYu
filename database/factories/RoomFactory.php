<?php

namespace Database\Factories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Room>
 */
class RoomFactory extends Factory
{
    protected $model = \App\Models\Room::class;

    public function definition(): array
    {
        return [
           'room_type_id' => null,
            'room_number'  => (string) $this->faker->unique()->numberBetween(100, 999),
            'room_name'    => $this->faker->words(2, true),
            'floor'        => $this->faker->numberBetween(1, 4),
            'price'        => $this->faker->numberBetween(400000, 5000000),
            'capacity'     => $this->faker->numberBetween(2, 4),
            'status'       => $this->faker->randomElement(['Available', 'Occupied', 'Maintenance']),
            'image'        => null,
            'description'  => $this->faker->sentence(10),
        ];
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'Available']);
    }

    public function occupied(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'Occupied']);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'Maintenance']);
    }
}
