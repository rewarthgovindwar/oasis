<?php

namespace App\Http\Middleware;

use Closure;
use DB;
use Schema;

class RouteServe
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Schema::hasTable('sm_general_settings') && Schema::hasTable('users')) {
            $data = DB::table('sm_general_settings')->first();
            if (@$data->system_purchase_code !== '') {
                return $next($request);
            }

            return view('install.verified_code');

        }

        return $next($request);
    }
}
