<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $roomTypes = [
            [
                'name'        => 'Superior Room',
                'bed_type'    => 'Twin Bed',
                'room_size'   => 24,
                'total_rooms' => 5,
                'status'      => 'active',
                'description' => 'Kamar nyaman dengan desain modern, cocok untuk perjalanan bisnis maupun liburan singkat.',
            ],
            [
                'name'        => 'Deluxe Room',
                'bed_type'    => 'Queen Bed',
                'room_size'   => 30,
                'total_rooms' => 4,
                'status'      => 'active',
                'description' => 'Kamar lebih luas dengan interior elegan dan fasilitas lengkap untuk kenyamanan maksimal.',
            ],
            [
                'name'        => 'Deluxe Ocean View',
                'bed_type'    => 'Queen Bed',
                'room_size'   => 32,
                'total_rooms' => 1,
                'status'      => 'active',
                'description' => 'Kamar Deluxe dengan pemandangan laut langsung dari balkon pribadi, ideal untuk suasana romantis.',
            ],
            [
                'name'        => 'Executive Room',
                'bed_type'    => 'King Bed',
                'room_size'   => 38,
                'total_rooms' => 3,
                'status'      => 'active',
                'description' => 'Kamar eksklusif dengan ruang kerja terpisah dan akses ke fasilitas premium hotel.',
            ],
            [
                'name'        => 'Family Suite',
                'bed_type'    => 'Two Queen Beds',
                'room_size'   => 48,
                'total_rooms' => 2,
                'status'      => 'active',
                'description' => 'Suite luas untuk keluarga dengan ruang tamu terpisah dan dua tempat tidur queen size.',
            ],
            [
                'name'        => 'Presidential Suite',
                'bed_type'    => 'King Bed',
                'room_size'   => 75,
                'total_rooms' => 5,
                'status'      => 'active',
                'description' => 'Suite paling mewah dengan ruang tamu privat, jacuzzi, dan layanan butler 24 jam.',
            ],
        ];

        // Pemetaan nama Room Type -> daftar nama Facility yang dihubungkan
        $facilityMap = [
            'Superior Room' => [
                'Free WiFi',
                'Smart TV',
                'Breakfast Included',
            ],
            'Deluxe Room' => [
                'Free WiFi',
                'Swimming Pool',
                'Smart TV',
                'Breakfast Included',
            ],
            'Deluxe Ocean View' => [
                'Free WiFi',
                'Swimming Pool',
                'Smart TV',
                'Breakfast Included',
                'Airport Shuttle',
            ],
            'Executive Room' => [
                'Free WiFi',
                'Swimming Pool',
                'Gym Center',
                'Restaurant',
                'Laundry Service',
            ],
            'Family Suite' => [
                'Free WiFi',
                'Restaurant',
                'Smart TV',
                'Breakfast Included',
            ],
            'Presidential Suite' => Facility::pluck('name')->all(), // semua fasilitas
        ];

        foreach ($roomTypes as $data) {
            $roomType = RoomType::create($data);

            $facilityIds = Facility::whereIn('name', $facilityMap[$data['name']])
                ->pluck('id')
                ->all();

            $roomType->facilityLinks()->sync($facilityIds);
        }
    }
}
