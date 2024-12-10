<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;

class WidgetClientDetection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if($request->header('X-CLIENT-ID')) {
            Config::set('client_id', $request->header('X-CLIENT-ID'));
        }
        return $next($request);
    }
}
