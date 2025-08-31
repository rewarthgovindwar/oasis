<?php

namespace Modules\Chat\Repositories;

use Modules\Chat\Entities\Conversation;
use Modules\Chat\Entities\GroupMessageRecipient;
use Modules\Chat\Entities\GroupMessageRemove;

class ConversationRepository
{
    protected $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function oneToOneDeleteNotAuthor(Conversation $conversation): bool
    {
        $conversation->update([
            'deleted_by_to' => 1,
        ]);

        return true;
    }

    public function oneToOneDeleteByAuthor(Conversation $conversation): bool
    {
        $conversation->delete();

        return true;
    }

    public function groupMessageDelete(GroupMessageRecipient $groupMessageRecipient)
    {
        if ($groupMessageRecipient->user_id === auth()->id()) {
            $groupMessageRecipient->conversation()->delete();

            return $groupMessageRecipient->delete();
        }

        GroupMessageRemove::create([
            'user_id' => auth()->id(),
            'group_message_recipient_id' => $groupMessageRecipient->id,
        ]);

        return true;
    }
}
