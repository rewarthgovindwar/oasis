<?php

namespace App\Http;

use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\HttpsProtocol;
use App\Http\Middleware\ThemeCheckMiddleware;
use App\Http\Middleware\UserRolePermission;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        Middleware\TrustProxies::class,
        // \RenatoMarinho\LaravelPageSpeed\Middleware\InlineCss::class,
        // \RenatoMarinho\LaravelPageSpeed\Middleware\RemoveComments::class,
        // \RenatoMarinho\LaravelPageSpeed\Middleware\CollapseWhitespace::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // \App\Http\Middleware\RouteServe::class,
            Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            HttpsProtocol::class,
            Middleware\Localization::class,
            CheckMaintenanceMode::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'RouteServe' => Middleware\RouteServe::class,
        'auth' => Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'CheckUserMiddleware' => Middleware\CheckUserMiddleware::class,
        'CheckDashboardMiddleware' => Middleware\CheckDashboardMiddleware::class,
        'StudentMiddleware' => Middleware\StudentMiddleware::class,
        'AlumniMiddleware' => Middleware\AlumniMiddleware::class,
        'ParentMiddleware' => Middleware\ParentMiddleware::class,
        'CustomerMiddleware' => Middleware\CustomerMiddleware::class,
        'PM' => Middleware\ProductMiddleware::class,
        'cors' => Middleware\Cors::class,
        'XSS' => Middleware\XSS::class,
        'SAMiddleware' => Middleware\SAMiddleware::class,
        'subscriptionAccessUrl' => Middleware\SubscriptionAccessUrl::class,
        'userRolePermission' => UserRolePermission::class,
        'json.response' => Middleware\ForceJsonResponse::class,
        'subdomain' => Middleware\SubdomainMiddleware::class,
        'module' => Middleware\ModulePermissionMiddleware::class,
        'testExam' => \Modules\OnlineExam\Http\Middleware\TestExamMiddleware::class,
        '2fa' => Middleware\TwoFactorMiddleware::class,
        'fees_due_check' => Middleware\FeesDueCheckMiddleware::class,
        'ThemeCheckMiddleware' => ThemeCheckMiddleware::class,

    ];
}
