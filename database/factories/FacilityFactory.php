<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Facility>
 */
class FacilityFactory extends Factory
{
    protected $model = \App\Models\Facility::class;

    /**
     * Default definition (dipakai kalau Facility::factory() dipanggil tanpa
     * override). FacilitySeeder akan meng-override 'name', 'icon', dan
     * 'description' dengan data realistis lewat state(), jadi definition
     * default di sini cukup sebagai fallback generik.
     */
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
