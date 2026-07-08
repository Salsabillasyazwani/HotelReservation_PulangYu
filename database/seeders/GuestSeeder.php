<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GuestSeeder extends Seeder
{
    /**
     * Belum ada user Customer sama sekali di UserSeeder (cuma 1 admin),
     * jadi seeder ini sekalian membuat 10 user ber-role Customer,
     * lalu membuatkan 1 Guest profile untuk masing-masing user tersebut.
     *
     * Tidak mengubah UserFactory yang sudah ada, cuma memanggilnya
     * dengan state() tambahan (role_id, username, dst) sesuai fillable
     * User yang sudah ada.
     */
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
