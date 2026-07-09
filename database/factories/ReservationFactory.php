<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
class ReservationFactory extends Factory
{
    protected $model = \App\Models\Reservation::class;

    public function definition(): array
    {
        $checkIn  = Carbon::instance($this->faker->dateTimeBetween('+3 days', '+30 days'));
        $nights   = $this->faker->numberBetween(1, 5);
        $checkOut = (clone $checkIn)->addDays($nights);

        return [
            'user_id' => null,
            'room_id' => null,
            'promotion_id'        => null,
            'guest_name'          => $this->faker->name(),
            'phone'               => '08' . $this->faker->numerify('##########'),
            'email'               => $this->faker->safeEmail(),
            'identity_number'     => $this->faker->numerify('################'),
            'nationality'         => 'Indonesia',
            'check_in'            => $checkIn->toDateString(),
            'check_out'           => $checkOut->toDateString(),
            'nights'              => $nights,
            'guests'              => $this->faker->numberBetween(1, 4),
            'reservation_status'  => 'Pending',
            'payment_method'      => $this->faker->randomElement(['Cash', 'Bank Transfer', 'Credit Card', 'QRIS', 'E-Wallet']),
            'payment_status'      => 'Unpaid',
            'price_per_night'     => $this->faker->numberBetween(450000, 1500000),
            'deposit'             => 0,
            'tax'                 => 0,
            'discount'            => 0,
            'additional_charges'  => 0,
            'total_amount'        => 0,
            'special_request'     => $this->faker->optional(0.4)->sentence(8),
            'notes'               => null,
            'cancellation_reason' => null,
            'actual_check_in'     => null,
            'actual_check_out'    => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            $checkIn  = Carbon::instance($this->faker->dateTimeBetween('+5 days', '+45 days'));
            $nights   = $this->faker->numberBetween(1, 5);
            $checkOut = (clone $checkIn)->addDays($nights);

            return [
                'check_in'           => $checkIn->toDateString(),
                'check_out'          => $checkOut->toDateString(),
                'reservation_status' => 'Pending',
                'payment_status'     => 'Unpaid',
                'deposit'            => 0,
                'actual_check_in'    => null,
                'actual_check_out'   => null,
            ];
        });
    }
    public function confirmed(): static
    {
        return $this->state(function (array $attributes) {
            $checkIn  = Carbon::instance($this->faker->dateTimeBetween('+3 days', '+30 days'));
            $nights   = $this->faker->numberBetween(1, 7);
            $checkOut = (clone $checkIn)->addDays($nights);

            return [
                'check_in'           => $checkIn->toDateString(),
                'check_out'          => $checkOut->toDateString(),
                'reservation_status' => 'Confirmed',
                'payment_status'     => $this->faker->randomElement(['Paid', 'Partial']),
                'actual_check_in'    => null,
                'actual_check_out'   => null,
            ];
        });
    }

    public function checkedIn(): static
    {
        return $this->state(function (array $attributes) {
            $checkIn  = Carbon::instance($this->faker->dateTimeBetween('-3 days', '-1 days'));
            $nights   = $this->faker->numberBetween(2, 6);
            $checkOut = (clone $checkIn)->addDays($nights);

            return [
                'check_in'           => $checkIn->toDateString(),
                'check_out'          => $checkOut->toDateString(),
                'reservation_status' => 'Checked In',
                'payment_status'     => $this->faker->randomElement(['Paid', 'Partial']),
                'actual_check_in'    => $checkIn->copy()->setTime($this->faker->numberBetween(12, 16), 0),
                'actual_check_out'   => null,
            ];
        });
    }

    /**
     * Reservasi sudah selesai sepenuhnya (tamu sudah check-out).
     */
    public function checkedOut(): static
    {
        return $this->state(function (array $attributes) {
            $checkIn  = Carbon::instance($this->faker->dateTimeBetween('-60 days', '-10 days'));
            $nights   = $this->faker->numberBetween(1, 5);
            $checkOut = (clone $checkIn)->addDays($nights);

            return [
                'check_in'           => $checkIn->toDateString(),
                'check_out'          => $checkOut->toDateString(),
                'reservation_status' => 'Checked Out',
                'payment_status'     => 'Paid',
                'actual_check_in'    => $checkIn->copy()->setTime($this->faker->numberBetween(12, 16), 0),
                'actual_check_out'   => $checkOut->copy()->setTime($this->faker->numberBetween(9, 12), 0),
            ];
        });
    }

    /**
     * Reservasi dibatalkan.
     */
    public function cancelled(): static
    {
        return $this->state(function (array $attributes) {
            $checkIn  = Carbon::instance($this->faker->dateTimeBetween('-15 days', '+15 days'));
            $nights   = $this->faker->numberBetween(1, 4);
            $checkOut = (clone $checkIn)->addDays($nights);

            return [
                'check_in'            => $checkIn->toDateString(),
                'check_out'           => $checkOut->toDateString(),
                'reservation_status'  => 'Cancelled',
                'payment_status'      => $this->faker->randomElement(['Refunded', 'Unpaid']),
                'deposit'             => 0,
                'cancellation_reason' => $this->faker->randomElement([
                    'Tamu membatalkan karena perubahan jadwal perjalanan.',
                    'Pembayaran tidak diselesaikan sebelum batas waktu.',
                    'Tamu menemukan akomodasi lain.',
                    'Permintaan pembatalan dari pihak tamu.',
                ]),
                'actual_check_in'     => null,
                'actual_check_out'    => null,
            ];
        });
    }
}
