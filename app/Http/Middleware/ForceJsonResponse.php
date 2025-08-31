<?php

namespace App\Http\Middleware;

use App\ApiBaseMethod;
use Closure;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $request->headers->set('Accept', 'application/json');

            return $next($request);
        }

        return $next($request);

    }
}
