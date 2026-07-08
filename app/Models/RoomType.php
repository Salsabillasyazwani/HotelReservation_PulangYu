<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bed_type',
        'room_size',
        'total_rooms',
        'status',
        'description',
    ];

    // Supaya @json($roomType) & akses ->price/->capacity/->image/->facilities
    // tetap jalan tanpa mengubah Blade.
    protected $appends = ['price', 'capacity', 'image', 'facilities'];

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function facilityLinks()
    {
        return $this->belongsToMany(
            Facility::class,
            'facility_room_type',
            'room_type_id',
            'facility_id'
        )->withTimestamps();
    }

    // Harga termurah dari semua room di tipe ini (fallback 0 kalau belum ada room)
    public function getPriceAttribute()
    {
        return (float) ($this->rooms()->min('price') ?? 0);
    }

    // Kapasitas terbesar dari room di tipe ini
    public function getCapacityAttribute()
    {
        return (int) ($this->rooms()->max('capacity') ?? 0);
    }

    // Foto dari room pertama yang punya foto
    public function getImageAttribute()
    {
        return optional(
            $this->rooms()->whereNotNull('image')->first()
        )->image;
    }

    // Array nama fasilitas, dipakai Blade: is_array($roomType->facilities)
    public function getFacilitiesAttribute()
    {
        return $this->facilityLinks()->pluck('name')->all();
    }
}
