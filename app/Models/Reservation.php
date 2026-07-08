<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reservation_code',
        'room_id',
        'promotion_id',
        'guest_name',
        'phone',
        'email',
        'identity_number',
        'nationality',
        'check_in',
        'check_out',
        'nights',
        'guests',
        'reservation_status',
        'payment_method',
        'payment_status',
        'price_per_night',
        'deposit',
        'tax',
        'discount',
        'additional_charges',
        'total_amount',
        'special_request',
        'notes',
        'cancellation_reason',
        'actual_check_in',
        'actual_check_out',
    ];

    protected $casts = [
        'check_in'          => 'date',
        'check_out'         => 'date',
        'actual_check_in'   => 'datetime',
        'actual_check_out'  => 'datetime',
        'price_per_night'   => 'decimal:2',
        'deposit'           => 'decimal:2',
        'tax'               => 'decimal:2',
        'discount'          => 'decimal:2',
        'additional_charges'=> 'decimal:2',
        'total_amount'      => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function (Reservation $reservation) {
            if (empty($reservation->reservation_code)) {
                $reservation->reservation_code = static::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $year = date('Y');
        $last = static::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->first();

        $next = $last ? ((int) substr($last->reservation_code, -5)) + 1 : 1;

        return 'RSV-' . $year . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
    public function user()
{
    return $this->belongsTo(\App\Models\User::class);
}
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Hitung nights, tax, discount, dan total_amount.
     *
     * Kalau $discountAmount diisi manual (bukan null), dipakai apa adanya
     * (dipakai misalnya kalau suatu saat butuh override manual). Kalau
     * dibiarkan null (default) dan reservasi ini punya promotion_id yang
     * ter-attach, discount dihitung otomatis dari Promotion::calculateDiscount().
     * Kalau tidak ada promotion sama sekali, discount = 0.
     */
    public function calculateTotals(float $taxRate = 0.10, ?float $discountAmount = null): void
    {
        $checkIn  = \Carbon\Carbon::parse($this->check_in);
        $checkOut = \Carbon\Carbon::parse($this->check_out);

        $this->nights = max(1, $checkIn->diffInDays($checkOut));

        $subTotal = $this->price_per_night * $this->nights;
        $this->tax = round($subTotal * $taxRate, 2);

        if ($discountAmount !== null) {
            $this->discount = $discountAmount;
        } elseif ($this->promotion_id && $this->promotion) {
            $this->discount = $this->promotion->calculateDiscount($subTotal);
        } else {
            $this->discount = 0;
        }

        $this->total_amount = $subTotal + $this->tax - $this->discount + $this->additional_charges;
    }

    public static function hasOverlap(int $roomId, string $checkIn, string $checkOut, ?int $ignoreId = null): bool
    {
        $query = static::where('room_id', $roomId)
            ->whereNotIn('reservation_status', ['Cancelled', 'Checked Out'])
            ->where(function (Builder $q) use ($checkIn, $checkOut) {
                $q->where('check_in', '<', $checkOut)
                  ->where('check_out', '>', $checkIn);
            });

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;

        return $query->where(function ($q) use ($term) {
            $q->where('reservation_code', 'like', "%{$term}%")
              ->orWhere('guest_name', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('reservation_status', $status) : $query;
    }

    public function scopePaymentStatus(Builder $query, ?string $status): Builder
    {
        return $status ? $query->where('payment_status', $status) : $query;
    }

    public function scopeRoomType(Builder $query, ?string $roomType): Builder
    {
        if (!$roomType) return $query;

        return $query->whereHas('room.roomType', function ($q) use ($roomType) {
            $q->where('name', $roomType);
        });
    }

    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) $query->whereDate('check_in', '>=', $from);
        if ($to)   $query->whereDate('check_out', '<=', $to);
        return $query;
    }
}
