<?php

namespace App\View\Components;

use App\SmHeaderMenuManager;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class HeaderContentMobileMenu extends Component
{
    public function __construct()
    {
        //
    }

    public function render(): View|Closure|string
    {
        $menus = SmHeaderMenuManager::where('school_id', app('school')->id)->where('theme', 'edulia')->whereNull('parent_id')->orderBy('position')->get();

        return view('components.'.activeTheme().'.header-content-mobile-menu', ['menus' => $menus]);
    }
}
