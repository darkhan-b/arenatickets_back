<?php

namespace App\Http\Middleware;

use App\Models\General\MenuItem;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Models\General\Setting;
use Stevebauman\Location\Facades\Location;

class SetLocaleAPI
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $lang = $request->header('Accept-language');
        if(!in_array($lang,['en','kz','ru'])) $lang = 'ru';
        app()->setLocale($lang);
        return $next($request);
    }
}
