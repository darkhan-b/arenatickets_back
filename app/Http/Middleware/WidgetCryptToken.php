<?php

namespace App\Http\Middleware;

use App\Models\General\OneTimeAuth;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class WidgetCryptToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if($request->bearerToken()) {
            try {
                $decrypted = Crypt::decryptString($request->bearerToken());
                $ota = OneTimeAuth::where('token',$decrypted)->first();
                if($ota) {
                    Auth::loginUsingId($ota->user_id);
                }
            } catch(\Exception $e) {}
        }
        return $next($request);
    }
}
