<?php

namespace App\Http\Middleware;

use Spatie\Crypto\Rsa\PrivateKey;
use Closure;

class APIPartnerSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if(app()->environment() === 'development') {
            return $next($request);
        }
        $user = $request->user;
        $privateKey = PrivateKey::fromString($user->private_key);
        $timestamp = $request->header('TIMESTAMP');
        $signatureReceived = $request->header('SIGNATURE');
        if(!$signatureReceived) {
            return response()->json([
                'error' => 'Подпись отсутствует',
            ], 401, [], JSON_UNESCAPED_UNICODE);
        }
        $signature = base64_decode($signatureReceived);
        $signatureDecrypted = $privateKey->decrypt($signature);
        if($signatureDecrypted != $request->getPathInfo().$timestamp) {
            return response()->json([
                'error' => 'Неверная подпись',
            ], 401, [], JSON_UNESCAPED_UNICODE);
        }
        $currentTimestamp = time();
        $diff = $currentTimestamp - $timestamp; // in seconds
        if(abs($diff) > PARTNER_API_TIMESTAMP_LIMIT) {
            return response()->json([
                'error' => 'Временная метка истекла',
            ], 401, [], JSON_UNESCAPED_UNICODE);
        }
        return $next($request);
    }
}
