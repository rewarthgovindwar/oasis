<?php

namespace App\View\Components;

use App\Models\FrontResult;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FrontendResult extends Component
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
        $frontResults = FrontResult::where('school_id', app('school')->id)->get();

        return view('components.'.activeTheme().'.frontend-result', ['frontResults' => $frontResults]);
    }
}
