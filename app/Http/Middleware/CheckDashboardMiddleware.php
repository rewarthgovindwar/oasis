<?php

namespace App\Http\Middleware;

use App\GlobalVariable;
use App\User;
use Closure;
use Illuminate\Support\Facades\Session;

class CheckDashboardMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (User::checkAuth() == false || User::checkAuth() == null) {
            return redirect()->route('system.config');
        }

        //        session_start();
        $role_id = Session::get('role_id');
        if ($role_id == 2) {
            // return $next($request);
            return redirect('student-dashboard');
        }

        if ($role_id == GlobalVariable::isAlumni()) {
            return redirect('alumni-dashboard');
        }

        if ($role_id == 3) {
            return redirect('parent-dashboard');
        }

        if ($role_id !== '') {
            return $next($request);
        }

        return redirect('login');

    }
}
