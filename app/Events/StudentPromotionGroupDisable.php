<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentPromotionGroupDisable
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $sectionId;

    public $classId;

    public function __construct($sectionId, $classId)
    {
        $this->sectionId = $sectionId;
        $this->classId = $classId;
    }
}
