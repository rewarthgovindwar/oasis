<?php

namespace App\View\Components;

use App\Models\FrontClassRoutine;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FrontendClassRoutine extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $frontClassRoutines = FrontClassRoutine::where('school_id', app('school')->id)->get();

        return view('components.'.activeTheme().'.frontend-class-routine', ['frontClassRoutines' => $frontClassRoutines]);
    }
}
