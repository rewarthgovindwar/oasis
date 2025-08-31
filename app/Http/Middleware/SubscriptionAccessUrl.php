<?php

namespace App\Http\Middleware;

use Closure;

class SubscriptionAccessUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (isSubscriptionEnabled()) {
            $temp = \Modules\Saas\Entities\SmPackagePlan::isSubscriptionAutheticate();
            if ($temp == true) {
                return $next($request);
            }

            return redirect('subscription/package-list');

        }

        return $next($request);

    }
}
