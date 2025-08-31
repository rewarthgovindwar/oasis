<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

class HttpsProtocol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (env('FORCE_HTTPS') && (! $request->secure() && App::environment() == 'production')) {
            $currentURL = Request::url();
            $check = mb_strstr($currentURL, 'http://');
            if ($check) {
                $currentURL = str_replace('http', 'https', $currentURL);

                return redirect()->to($currentURL);
            }
        }

        return $next($request);
    }
}
