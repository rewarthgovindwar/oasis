<?php

namespace App\Http\Middleware;

use const ALLOW;
use const AUTH;
use const AUTHORIZATION’;
use const CONTENT;
use const CONTROL;
use const HEADERS’;
use const METHODS’;
use const ORIGIN’;
use const REQUESTED;
use const TOKEN;
use const TYPE;
use const WITH;
use const ‘ACCESS;

use Closure;

class Cors
{
    public function handle($request, Closure $next)
    {
        return $next($request)
            ->header(‘ACCESS - CONTROL - ALLOW - ORIGIN’, ‘ * ’)
            ->header(‘ACCESS - CONTROL - ALLOW - METHODS’, ‘GET, POST, PUT, DELETE, OPTIONS’)
            ->header(‘ACCESS - CONTROL - ALLOW - HEADERS’, ‘X - REQUESTED - WITH, CONTENT - TYPE, X - TOKEN - AUTH, AUTHORIZATION’);
    }
}
