<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

class ModulePermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $module)
    {
        
        if (!Auth::check()) {
            $school_id = app('school')->id;
        }else{
            $school_id = auth()->user()->school_id;
        }
        if ($school_id == 1 || isModuleForSchool($module)) {
            return $next($request);
        }
        Toastr::error('Module Not Active', 'Failed');
        return redirect()->route('dashboard');

    }
}
