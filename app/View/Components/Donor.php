<?php

namespace App\View\Components;

use App\Models\SmDonor;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Donor extends Component
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
        $data['donors'] = SmDonor::where('school_id', app('school')->id)->where('show_public', 1)->get();

        return view('components.'.activeTheme().'.donor', $data);
    }
}
