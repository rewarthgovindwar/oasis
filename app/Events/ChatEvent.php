<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Modules\Chat\Entities\Conversation;

class ChatEvent implements ShouldBroadcast
{
    use InteractsWithSockets;
    use SerializesModels;

    public $message;

    public function __construct(Conversation $conversation)
    {
        $this->message = $conversation;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('single-chat.'.$this->message->to_id);
    }
}
