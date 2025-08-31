<?php

namespace App;

use App\Scopes\ActiveStatusSchoolScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmRoomList extends Model
{
    use HasFactory;

    public function dormitory()
    {
        return $this->belongsTo(SmDormitoryList::class, 'dormitory_id');
    }

    public function roomType()
    {
        return $this->belongsTo(SmRoomType::class, 'room_type_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ActiveStatusSchoolScope);
    }
}
