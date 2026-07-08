<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = ['name', 'icon', 'description', 'status'];

    public function roomTypes()
    {
        return $this->belongsToMany(
            RoomType::class,
            'facility_room_type',
            'facility_id',
            'room_type_id'
        )->withTimestamps();
    }
}
