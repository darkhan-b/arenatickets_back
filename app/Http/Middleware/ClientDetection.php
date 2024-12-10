<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;

class ClientDetection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $user = $request->user();
        Config::set('client_id', $user->client_id);
        $superadmin = $user->can('admin_clients');
        Config::set('superadmin', $superadmin);
        if(!$user->client_id && $request->client_id && $superadmin) {
            Config::set('client_id', $request->client_id);
        }
        return $next($request);
    }
}
