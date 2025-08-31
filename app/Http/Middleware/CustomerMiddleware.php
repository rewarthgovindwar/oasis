<?php

namespace App\Http\Middleware;

use Closure;
use Modules\RolePermission\Entities\InfixRole;

class CustomerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        session_start();
        $role_id = session()->get('role_id');
        $is_saas = InfixRole::where('id', $role_id)->first('is_saas')->is_saas;
        if ($is_saas == 1) {

            return redirect('saasStaffDashboard');
        }

        if ($role_id !== '') {
            return redirect('customer-dashboard');
        }

        return redirect('login');

    }
}
