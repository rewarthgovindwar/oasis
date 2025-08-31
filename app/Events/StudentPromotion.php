<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentPromotion
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $student_promotion;

    public $student;

    public function __construct($student_promotion, $student)
    {
        $this->student_promotion = $student_promotion;
        $this->student = $student;
    }
}
