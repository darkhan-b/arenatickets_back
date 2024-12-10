<?php

namespace App\Http\Middleware;

use App\Models\Specific\APIPartner;
use Closure;
use Illuminate\Support\Facades\Config;

class APIPartnerToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        return $next($request);
        $token = $request->header('X-PARTNER-TOKEN');
        $user = APIPartner::withoutGlobalScopes()
            ->where(['token' => $token])
            ->active()
            ->first();
        if(!$user) {
            return response()->json([
                'success' => false,
                'code' => 401,
                'message' => 'Авторизация не успешна '.$token,
            ], 401, [], JSON_UNESCAPED_UNICODE);
        }
        Config::set('client_id', $user->client_id);
        $request->merge(compact('user'));
        return $next($request);
    }
}
