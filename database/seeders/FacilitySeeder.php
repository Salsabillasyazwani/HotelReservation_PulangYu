<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;

class FacilitySeeder extends Seeder
{
    /**
     * Seed 10 fasilitas hotel yang realistis.
     * Semua fasilitas berstatus Active.
     */
    public function run(): void
    {
        $facilities = [
            [
                'name'        => 'Free WiFi',
                'icon'        => 'bi bi-wifi',
                'description' => 'Akses internet berkecepatan tinggi gratis di seluruh area kamar dan hotel.',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Swimming Pool',
                'icon'        => 'bi bi-water',
                'description' => 'Kolam renang outdoor dengan pemandangan yang menyegarkan, tersedia sepanjang hari.',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Restaurant',
                'icon'        => 'bi bi-cup-hot',
                'description' => 'Restoran hotel dengan menu masakan lokal dan internasional untuk sarapan, makan siang, dan makan malam.',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Gym Center',
                'icon'        => 'bi bi-heart-pulse',
                'description' => 'Pusat kebugaran lengkap dengan peralatan olahraga modern, buka 24 jam.',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Spa',
                'icon'        => 'bi bi-flower1',
                'description' => 'Layanan spa dan pijat relaksasi profesional untuk menyegarkan tubuh dan pikiran.',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Laundry Service',
                'icon'        => 'bi bi-basket',
                'description' => 'Layanan cuci dan setrika pakaian tamu dengan waktu pengerjaan cepat.',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Airport Shuttle',
                'icon'        => 'bi bi-airplane',
                'description' => 'Layanan antar-jemput gratis dari dan menuju bandara untuk kenyamanan tamu.',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Meeting Room',
                'icon'        => 'bi bi-easel',
                'description' => 'Ruang pertemuan dengan fasilitas audio-visual lengkap untuk kebutuhan bisnis dan acara.',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Smart TV',
                'icon'        => 'bi bi-tv',
                'description' => 'Televisi pintar dengan akses saluran internasional dan layanan streaming di setiap kamar.',
                'status'      => 'Active',
            ],
            [
                'name'        => 'Breakfast Included',
                'icon'        => 'bi bi-egg-fried',
                'description' => 'Sarapan prasmanan gratis setiap hari dengan pilihan menu lokal dan internasional.',
                'status'      => 'Active',
            ],
        ];

        foreach ($facilities as $facility) {
    Facility::create($facility);
}
    }
}
