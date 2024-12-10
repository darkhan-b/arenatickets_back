<?php

namespace App\Http\Middleware;

use App\Models\Specific\APIPartner;
use App\Models\Specific\Timetable;
use Closure;

class APIPartnerShow
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        $user = $request->user;
        $timetableId = $request->id;
        $timetable = Timetable::find($timetableId);
        if(!$timetable) {
            return response()->json(['error' => 'Событие не найдено'], 404);
        }
        $show = $timetable->show;
        if(!$show || !$user->hasAccessToShow($show->id)) {
            return response()->json(['error' => 'Событие не найдено'], 404);
        }
        $request->merge(compact('timetable','show'));
        return $next($request);
    }
}
