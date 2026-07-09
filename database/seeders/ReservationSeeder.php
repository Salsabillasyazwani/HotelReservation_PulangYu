<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Promotion;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = Room::all()->shuffle()->values();
        $guests = Guest::with('user')->get();

        if ($rooms->count() < 20) {
            $this->command?->warn('Jumlah Room kurang dari 20, sebagian reservasi akan dilewati.');
        }

        if ($guests->isEmpty()) {
            $this->command?->warn('Tidak ada Guest ditemukan. Jalankan GuestSeeder terlebih dahulu.');
            return;
        }

        // Ada promosi aktif atau tidak (opsional, sesuai instruksi "Promotion bila tersedia").
        $promotions = Promotion::all();

        $statusPlan = array_merge(
            array_fill(0, 5, 'checkedOut'),   
            array_fill(0, 5, 'checkedIn'),    
            array_fill(0, 6, 'confirmed'),   
            array_fill(0, 2, 'pending'),     
            array_fill(0, 2, 'cancelled'),  
        );
        shuffle($statusPlan);

        $additionalChargeOptions = [0, 0, 0, 50000, 100000, 150000];

        foreach ($rooms->take(20) as $index => $room) {
            $status = $statusPlan[$index] ?? 'pending';
            $guest  = $guests[$index % $guests->count()];
            $user   = $guest->user;

            /** @var Reservation $reservation */
            $reservation = Reservation::factory()->{$status}()->make();

            $reservation->user_id         = $user?->id;
            $reservation->room_id         = $room->id;
            $reservation->guest_name      = $user?->name ?? $reservation->guest_name;
            $reservation->phone           = $guest->phone;
            $reservation->email           = $user?->email ?? $reservation->email;
            $reservation->identity_number = $guest->identity_number;
            $reservation->nationality     = fake()->randomElement([
                'Indonesia', 'Indonesia', 'Indonesia', 'Indonesia', 'Malaysia', 'Singapura',
            ]);
            $reservation->guests          = min(fake()->numberBetween(1, 4), max(1, $room->capacity));
            $reservation->price_per_night = $room->price;
            $reservation->additional_charges = fake()->randomElement($additionalChargeOptions);

            if (in_array($status, ['confirmed', 'checkedIn', 'checkedOut'])) {
                $reservation->deposit = round($room->price * fake()->randomFloat(2, 0.2, 0.3), -3);
            } else {
                $reservation->deposit = 0;
            }

            // Promotion opsional: hanya dipasang kalau ada Promotion yang
            // berlaku untuk room type ini, dengan peluang 30%.
            if ($promotions->isNotEmpty() && fake()->boolean(30)) {
                $applicable = $promotions->filter(function (Promotion $promo) use ($room) {
                    return empty($promo->rooms) || in_array($room->room_type_id, $promo->rooms ?? []);
                });

                if ($applicable->isNotEmpty()) {
                    $reservation->promotion_id = $applicable->random()->id;
                }
            }
            $reservation->calculateTotals(0.10);

            $reservation->save();
        }
    }
}
