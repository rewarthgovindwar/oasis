<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Modules\Chat\Entities\Conversation;
use Modules\Chat\Entities\Group;
use Modules\Chat\Entities\GroupMessageRecipient;

class GroupChatEvent implements ShouldBroadcast
{
    use InteractsWithSockets;
    use SerializesModels;

    public $group;

    public $thread;

    public $conversation;

    public $user;

    public function __construct(Group $group, GroupMessageRecipient $groupMessageRecipient, Conversation $conversation, $user)
    {
        $this->group = $group;
        $this->thread = $groupMessageRecipient;
        $this->conversation = $conversation;
        $this->user = $user;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('group-chat.'.$this->group->id);
    }
}
