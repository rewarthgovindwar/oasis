<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmSeatPlanChild extends Model
{
    use HasFactory;

    public static function usedRoomCapacity($room_id)
    {
        return self::where('room_id', $room_id)->sum('assign_students');
    }

    public function class_room()
    {
        return $this->belongsTo(SmClassRoom::class, 'room_id', 'id');
    }
}
