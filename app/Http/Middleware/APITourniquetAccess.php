<?php

namespace App\Http\Middleware;

use App\Models\Specific\APIPartner;
use Closure;
use Illuminate\Support\Facades\Config;

class APITourniquetAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {

        if($request->bearerToken() != env('TOURNIQUIET_BEARER_TOKEN')) {
            return response()->json([
                'code' 	  => 401,
                'message' => 'Авторизация не успешна',
            ], 401, [], JSON_UNESCAPED_UNICODE);
        }
		Config::set('client_id', 1);
		$request->headers->set('Accept', 'application/json');
        return $next($request);
    }
}
