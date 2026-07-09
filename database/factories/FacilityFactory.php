<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FacilityFactory extends Factory
{
    protected $model = \App\Models\Facility::class;
    public function definition(): array
    {
        return [
            'name'        => ucfirst($this->faker->unique()->words(2, true)),
            'icon'        => 'bi bi-check-circle',
            'description' => $this->faker->sentence(8),
            'status'      => 'Active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Inactive',
        ]);
    }
}
