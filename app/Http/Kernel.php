<?php

namespace App\Http;

use App\Http\Middleware\APIPartnerOrder;
use App\Http\Middleware\APIPartnerShow;
use App\Http\Middleware\APIPartnerToken;
use App\Http\Middleware\APIToken;
use App\Http\Middleware\APITourniquetAccess;
use App\Http\Middleware\ClientDetection;
use App\Http\Middleware\WidgetClientDetection;
use App\Http\Middleware\WidgetCryptToken;
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
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\MetaMiddleware::class
        ],

        'api' => [
//            'throttle:api',
//            'throttle:60,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetLocaleAPI::class,
//            \App\Http\Middleware\APIToken::class
        ],

        'partner' => [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetLocaleAPI::class,
            \App\Http\Middleware\APIPartnerToken::class,
            \App\Http\Middleware\APIPartnerSignature::class,
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
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'apitoken' => APIToken::class,
        'clientDetection' => ClientDetection::class,
        'widgetClientDetection' => WidgetClientDetection::class,
        'apipartnershow' => APIPartnerShow::class,
        'apipartnerorder' => APIPartnerOrder::class,
        'admin' => \App\Http\Middleware\Admin::class,
        'organizer' => \App\Http\Middleware\Organizer::class,
        'organizerOrManager' => \App\Http\Middleware\OrganizerOrManager::class,
        'basicauth' => \App\Http\Middleware\BasicAuth::class,
        'setlocale' => \App\Http\Middleware\SetLocale::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'cookies' => \App\Http\Middleware\EncryptCookies::class,
        'queued_cookies' => \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        'session' => \Illuminate\Session\Middleware\StartSession::class,
        'widgetCryptToken' => WidgetCryptToken::class,
		'tourniquetAccess' => APITourniquetAccess::class
    ];
}
