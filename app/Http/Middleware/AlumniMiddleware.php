<?php

namespace App\Http\Middleware;

use App\GlobalVariable;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AlumniMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (User::checkAuth() == false || User::checkAuth() == null) {
            return redirect()->route('system.config');
        }

        session_start();
        $role_id = Session::get('role_id');
        if ($role_id == GlobalVariable::isAlumni()) {
            return $next($request);
        }

        if ($role_id !== '') {
            return redirect('alumni-dashboard');
        }

        return redirect('login');

    }
}
