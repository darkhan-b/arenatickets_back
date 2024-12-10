<?php

namespace App\Http\Middleware;

use App\Models\General\Meta;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Models\General\Setting;

class MetaMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $url = $request->path();
        $locale = app()->getLocale();
        $url = str_replace($locale.'/', '', $url);
        if($url === $locale) $url = '/';
        if($url === '') $url = '/';
        $meta = Meta::where('url', $url)->first();
        View::share([
            'meta' => $meta, 
            'url' => $url
        ]);
        return $next($request);
    }
}
