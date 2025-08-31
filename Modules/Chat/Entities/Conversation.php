<?php

namespace Modules\Chat\Entities;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'chat_conversations';

    protected $fillable = [
        'from_id',
        'to_id',
        'message',
        'message_type',
        'status',
        'file_name',
        'to_type',
        'reply_to',
        'initial',
        'original_file_name',
        'reply',
        'forward',
        'deleted_by_to',
    ];

    public function getCreatedAtDiffHumanAttribute(): string
    {
        return Carbon::parse($this->created_at)->diffForHumans();
    }

    public function reply()
    {
        return $this->belongsTo(self::class, 'reply', 'id');
    }

    public function forwardFrom()
    {
        return $this->belongsTo(self::class, 'forward', 'id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_id', 'id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_id', 'id');
    }

    public function forMe(): bool
    {
        return $this->to_id === auth()->id();
    }

    public function fromMe(): bool
    {
        return $this->from_id === auth()->id();
    }

    public function replyId()
    {
        return $this->belongsTo(self::class, 'reply', 'id');
    }

    protected static function newFactory()
    {
        return \Modules\Chat\Database\factories\ConversationFactory::new();
    }
}
