<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuestFactory extends Factory
{
    protected $model = \App\Models\Guest::class;

    public function definition(): array
    {
        // NIK (Nomor Induk Kependudukan) realistis: 16 digit,
        // diawali kode wilayah acak supaya terasa nyata.
        $identityNumber = fake('id_ID')->numerify(fake()->randomElement(['31', '32', '33', '35', '51', '52', '61', '73']) . '##############');

        return [
            'user_id'         => User::factory(),
            'identity_number' => $identityNumber,
            'phone'           => '08' . fake('id_ID')->numerify('##########'),
            'address'         => fake('id_ID')->streetAddress() . ', ' . fake('id_ID')->city(),
        ];
    }
}
