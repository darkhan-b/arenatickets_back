<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\General\User;
use Illuminate\Support\Facades\Session;

class Organizer
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
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole('organizer')) return $next($request);
        }
        return response()->json(['error' => 'Недостаточно прав'], 403);
    }
}