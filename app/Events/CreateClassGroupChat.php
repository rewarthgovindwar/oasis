<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateClassGroupChat
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $assign_subject;

    public function __construct($assign_subject)
    {
        $this->assign_subject = $assign_subject;
    }
}
