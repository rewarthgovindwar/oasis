<?php

namespace App\View\Components;

use App\SmStaff;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FrontendTeacherList extends Component
{
    public function __construct()
    {
        //
    }

    public function render(): View|Closure|string
    {
        $data['teachers'] = SmStaff::where('is_saas', 0)
            ->where('school_id', app('school')->id)
            ->where('role_id', 4)
            ->with(['roles' => function ($query): void {
                $query->select('id', 'name');
            }])
            ->with(['departments' => function ($query): void {
                $query->select('id', 'name');
            }])
            ->with(['designations' => function ($query): void {
                $query->select('id', 'title');
            }])
            ->get();

        return view('components.'.activeTheme().'.frontend-teacher-list', $data);
    }
}
