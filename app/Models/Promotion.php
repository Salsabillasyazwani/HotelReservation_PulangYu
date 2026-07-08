<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'promo_code',
        'promo_name',
        'description',
        'discount_type',
        'discount_value',
        'minimum_booking',
        'maximum_discount',
        'banner',
        'rooms',
        'start_date',
        'end_date',
        'status',
        'quota',
    ];

    protected $casts = [
        'rooms' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'discount_value' => 'decimal:2',
        'minimum_booking' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function getComputedStatusAttribute(): string
    {
        if ($this->status === 'Inactive') {
            return 'Inactive';
        }

        $today = Carbon::today();

        if ($this->start_date && $today->lt($this->start_date)) {
            return 'Upcoming';
        }

        if ($this->end_date && $today->gt($this->end_date)) {
            return 'Expired';
        }

        return 'Active';
    }

    /**
     * Kolom `rooms` (json/array) di tabel promotions menyimpan ID dari
     * room_types.id (bukan lagi nama string hardcoded seperti sebelumnya).
     * Accessor ini me-resolve ID tsb ke [{id, name}, ...] dengan query ke
     * tabel room_types. Kalau suatu room type dihapus, ID-nya otomatis
     * tidak muncul di sini (aman, tidak error).
     *
     * Dipakai di Blade: $promotion->room_type_options
     */
    public function getRoomTypeOptionsAttribute(): array
    {
        if (empty($this->rooms)) {
            return [];
        }

        return RoomType::whereIn('id', $this->rooms)
            ->get(['id', 'name'])
            ->map(fn ($rt) => ['id' => $rt->id, 'name' => $rt->name])
            ->all();
    }

    /**
     * Hitung nominal discount dari subtotal, sudah memperhitungkan
     * maximum_discount (cap) dan tidak boleh melebihi subtotal itu sendiri.
     */
    public function calculateDiscount(float $subTotal): float
    {
        $discount = match ($this->discount_type) {
            'Percentage' => $subTotal * ((float) $this->discount_value / 100),
            'Fixed Amount', 'Voucher' => (float) $this->discount_value,
            default => 0,
        };

        if ($this->maximum_discount) {
            $discount = min($discount, (float) $this->maximum_discount);
        }

        return round(min($discount, $subTotal), 2);
    }

    /**
     * Cek apakah promo ini valid dipakai untuk room type & subtotal tertentu.
     * Return [bool $isValid, ?string $reason].
     */
    public function isApplicableTo(int $roomTypeId, float $subTotal): array
    {
        if ($this->computed_status !== 'Active') {
            return [false, 'Kode promo tidak aktif atau sudah kedaluwarsa.'];
        }

        if (!empty($this->rooms) && !in_array($roomTypeId, $this->rooms)) {
            return [false, 'Kode promo ini tidak berlaku untuk tipe kamar yang dipilih.'];
        }

        if ($this->minimum_booking && $subTotal < (float) $this->minimum_booking) {
            return [false, 'Minimum booking untuk promo ini adalah Rp ' . number_format($this->minimum_booking, 0, ',', '.') . '.'];
        }

        if (!is_null($this->quota) && $this->quota <= 0) {
            return [false, 'Kuota promo ini sudah habis.'];
        }

        return [true, null];
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('promo_name', 'like', "%{$term}%")
                ->orWhere('promo_code', 'like', "%{$term}%");
        });
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return ($type && $type !== 'All') ? $query->where('discount_type', $type) : $query;
    }

    public function scopeOfStatus(Builder $query, ?string $status): Builder
    {
        if (!$status || $status === 'All') {
            return $query;
        }

        $today = Carbon::today()->toDateString();

        return match ($status) {
            'Active' => $query->where('status', 'Active')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today),
            'Upcoming' => $query->where('status', 'Active')
                ->whereDate('start_date', '>', $today),
            'Expired' => $query->whereDate('end_date', '<', $today),
            'Inactive' => $query->where('status', 'Inactive'),
            default => $query,
        };
    }

    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('start_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('end_date', '<=', $to);
        }

        return $query;
    }
}
