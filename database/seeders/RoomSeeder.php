<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $roomTypes = RoomType::pluck('id', 'name');

        // Rentang harga (per malam, dalam Rupiah) sesuai kelas kamar.
        $priceRanges = [
            'Superior Room'      => [450000, 550000],
            'Deluxe Room'        => [650000, 750000],
            'Deluxe Ocean View'  => [850000, 950000],
            'Executive Room'     => [1100000, 1300000],
            'Family Suite'       => [1500000, 1700000],
            'Presidential Suite' => [3000000, 4500000],
        ];

        // Kapasitas tamu per kelas kamar.
        $capacityRanges = [
            'Superior Room'      => [2, 2],
            'Deluxe Room'        => [2, 2],
            'Deluxe Ocean View'  => [2, 3],
            'Executive Room'     => [2, 3],
            'Family Suite'       => [4, 4],
            'Presidential Suite' => [4, 6],
        ];

        $descriptions = [
            'Superior Room'      => 'Kamar Superior yang nyaman dengan pencahayaan alami dan perabotan modern.',
            'Deluxe Room'        => 'Kamar Deluxe dengan ruang lebih luas dan sentuhan interior elegan.',
            'Deluxe Ocean View'  => 'Kamar Deluxe dengan balkon pribadi menghadap langsung ke laut.',
            'Executive Room'     => 'Kamar Executive dengan area kerja terpisah, cocok untuk tamu bisnis.',
            'Family Suite'       => 'Suite keluarga dengan ruang tamu terpisah dan dua tempat tidur besar.',
            'Presidential Suite' => 'Suite mewah dengan ruang tamu privat, jacuzzi, dan pemandangan kota.',
        ];

        // Daftar 20 kamar: nomor kamar, lantai (diambil dari digit pertama nomor kamar), dan tipe kamarnya.
        $rooms = [
            ['room_number' => '101', 'floor' => 1, 'type' => 'Superior Room'],
            ['room_number' => '102', 'floor' => 1, 'type' => 'Superior Room'],
            ['room_number' => '103', 'floor' => 1, 'type' => 'Superior Room'],
            ['room_number' => '104', 'floor' => 1, 'type' => 'Superior Room'],
            ['room_number' => '105', 'floor' => 1, 'type' => 'Superior Room'],

            ['room_number' => '201', 'floor' => 2, 'type' => 'Deluxe Room'],
            ['room_number' => '202', 'floor' => 2, 'type' => 'Deluxe Room'],
            ['room_number' => '203', 'floor' => 2, 'type' => 'Deluxe Room'],
            ['room_number' => '204', 'floor' => 2, 'type' => 'Deluxe Room'],
            ['room_number' => '205', 'floor' => 2, 'type' => 'Deluxe Ocean View'],

            ['room_number' => '301', 'floor' => 3, 'type' => 'Executive Room'],
            ['room_number' => '302', 'floor' => 3, 'type' => 'Executive Room'],
            ['room_number' => '303', 'floor' => 3, 'type' => 'Executive Room'],
            ['room_number' => '304', 'floor' => 3, 'type' => 'Family Suite'],
            ['room_number' => '305', 'floor' => 3, 'type' => 'Family Suite'],

            ['room_number' => '401', 'floor' => 4, 'type' => 'Presidential Suite'],
            ['room_number' => '402', 'floor' => 4, 'type' => 'Presidential Suite'],
            ['room_number' => '403', 'floor' => 4, 'type' => 'Presidential Suite'],
            ['room_number' => '404', 'floor' => 4, 'type' => 'Presidential Suite'],
            ['room_number' => '405', 'floor' => 4, 'type' => 'Presidential Suite'],
        ];
.
        $statusPool = [
            'Available', 'Available', 'Available', 'Available',
            'Available', 'Available', 'Available',
            'Occupied', 'Occupied', 'Occupied',
            'Maintenance',
        ];

        foreach ($rooms as $index => $room) {
            $imageNumber = $index + 1;
            $type = $room['type'];

            [$minPrice, $maxPrice] = $priceRanges[$type];
            [$minCap, $maxCap] = $capacityRanges[$type];

            Room::factory()->state([
                'room_type_id' => $roomTypes[$type],
                'room_number'  => $room['room_number'],
                'room_name'    => $type . ' ' . $room['room_number'],
                'floor'        => $room['floor'],
                'price'        => fake()->numberBetween($minPrice, $maxPrice),
                'capacity'     => fake()->numberBetween($minCap, $maxCap),
                'status'       => fake()->randomElement($statusPool),
                'image'        => 'rooms/room' . $imageNumber . '.png',
                'description'  => $descriptions[$type],
            ])->create();
        }
    }
}
