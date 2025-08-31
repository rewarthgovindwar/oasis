<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Session;

class StudentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (User::checkAuth() === false || User::checkAuth() === null) {
            return redirect()->route('system.config');
        }

        session_start();
        $role_id = Session::get('role_id');

        if ($role_id === 2) {
            return $next($request);
        }

        if ($role_id !== '') {
            return redirect('chat.index');
        }

        return redirect('login');

    }
}
