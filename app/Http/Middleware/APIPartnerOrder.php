<?php

namespace App\Http\Middleware;

use App\Models\Specific\APIPartner;
use App\Models\Specific\Order;
use App\Models\Specific\Timetable;
use Closure;

class APIPartnerOrder
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
        $orderId = $request->id;
        $order = Order::find($orderId);
        if(!$order || $order->partner_id != $user->id) {
            return response()->json(['error' => 'Заказ не найден'], 404);
        }
        $request->merge(compact('order'));
        return $next($request);
    }
}
