<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'role_id' => 1,
            'name' => 'Administrator',
            'email' => 'admin@hotel.com',
            'password' => Hash::make('admin123'),
        ]);
    }
}
