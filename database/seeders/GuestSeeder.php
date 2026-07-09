<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuestSeeder extends Seeder
{
    public function run(): void
    {
        $customerRole = Role::where('name', 'Customer')->first();

        if (!$customerRole) {
            $customerRole = Role::create(['name' => 'Customer']);
        }

        $customerNames = [
            'Budi Santoso',
            'Siti Rahayu',
            'Andi Wijaya',
            'Dewi Lestari',
            'Rizky Pratama',
            'Putri Anggraini',
            'Agus Setiawan',
            'Maya Sari',
            'Fajar Nugroho',
            'Indah Permata',
        ];

        foreach ($customerNames as $index => $name) {
            $emailSlug = strtolower(str_replace(' ', '.', $name));

            $user = User::factory()->create([
                'role_id'  => $customerRole->id,
                'name'     => $name,
                'username' => 'customer' . ($index + 1),
                'email'    => $emailSlug . '@example.com',
                'password' => Hash::make('password'),
                'status'   => 'Active',
            ]);

            Guest::factory()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
