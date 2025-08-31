<?php

namespace App\Http\Middleware;

use App\Models\MaintenanceSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    protected $except = [
        'login', 'login/*', 'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $c = Storage::exists('.app_installed') ? Storage::get('.app_installed') : false;
        if (! $c) {
            return $next($request);
        }

        if (auth()->user() && auth()->user()->is_administrator == 'yes') {
            return $next($request);
        }

        if ($this->inExceptArray($request)) {
            return $next($request);
        }

        SaasSchool();
        $setting = MaintenanceSetting::first();

        if ($setting && $setting->maintenance_mode) {
            if (auth()->check()) {
                $check = in_array(auth()->user()->role_id, $setting->applicable_for);
            } else {
                $check = in_array('front', $setting->applicable_for);
            }

            if ($check) {
                abort(503);
            }
        }

        return $next($request);
    }

    protected function inExceptArray($request): bool
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
