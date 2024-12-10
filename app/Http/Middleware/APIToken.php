<?php

namespace App\Http\Middleware;

use Closure;

class APIToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if(env('X_API_TOKEN') == $request->header('X-API-TOKEN')) {
            return $next($request);
        }
        return response()->json([
            'success' => false,
            'code' => 401,
            'message' => 'Авторизация не успешна',
        ], 401, [], JSON_UNESCAPED_UNICODE);
    }
}
