<?php

namespace App\View\Components;

use App\Models\SmExpertTeacher;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TeacherList extends Component
{
    public $count;

    public $column;

    /**
     * Create a new component instance.
     */
    public function __construct($count = 3, $column = 4)
    {
        $this->count = $count;
        $this->column = $column;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $staffs = SmExpertTeacher::where('school_id', app('school')->id)
            ->with('staff.designations')
            ->take($this->count)
            ->orderBy('position')
            ->get();

        return view('components.'.activeTheme().'.teacher-list', ['staffs' => $staffs]);
    }
}
