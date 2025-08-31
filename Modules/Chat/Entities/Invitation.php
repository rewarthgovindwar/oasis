<?php

namespace Modules\Chat\Entities;

use App\Models\InvitationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    use HasFactory;

    protected $table = 'chat_invitations';

    protected $fillable = [
        'from',
        'to',
        'status',
    ];

    public function requestFrom()
    {
        return $this->belongsTo(User::class, 'from');
    }

    public function requestTo()
    {
        return $this->belongsTo(User::class, 'to');
    }

    // == IndixEdu ==
    public function type()
    {
        return $this->hasOne(InvitationType::class, 'invitation_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($invite): void {
            $invite->type()->delete();
        });
    }

    protected static function newFactory()
    {
        return \Modules\Chat\Database\factories\InvitationFactory::new();
    }
}
