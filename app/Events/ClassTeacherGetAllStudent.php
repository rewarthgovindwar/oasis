<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClassTeacherGetAllStudent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $assign_class_teacher;

    public $class_teacher;

    public $type;

    public function __construct($assign_class_teacher, $class_teacher, $type = 'store')
    {
        $this->assign_class_teacher = $assign_class_teacher;
        $this->class_teacher = $class_teacher;
        $this->type = $type;
    }
}
