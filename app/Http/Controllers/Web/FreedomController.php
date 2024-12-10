<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\API\FreedomPayAPI;
use App\Models\API\JysanAPI;
use App\Models\Specific\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class FreedomController extends Controller {

    public function callback($action, $client_id, Request $request) {
        Log::error('freedom callback '.$action);
        Log::error($request->all());
        Config::set('client_id', $client_id);
        switch ($action) {
            case "check":
                return response($this->freedomCheck($request), 200)->header('Content-Type', 'text/xml');
            case "result":
                return response($this->freedomResult($request), 200)->header('Content-Type', 'text/xml');
            default:
                abort('404');
        }
    }

    public function freedomCheck(Request $request) {
        $order = Order::find($request->pg_order_id);
        $freedomApi = new FreedomPayAPI($order?->legal_entity_id);
        if(!$order) {
            return $freedomApi->responseXML('check','error','Ошибка обработки, заказ не найден');
        }
        if ($request->pg_amount == $order->price) {
            return $freedomApi->responseXML('check','ok','Суммы совпадают');
        }
        return $freedomApi->responseXML('check', 'rejected', 'Суммы не совпадают');;
    }

    public function freedomResult(Request $request) {
        $params = $request->all();
        $order = Order::find($request->pg_order_id);
        $freedomApi = new FreedomPayAPI($order?->legal_entity_id);
        if(!$order) {
            Log::error('order not found '.$request->pg_order_id);
            return $freedomApi->responseXML('result','rejected','Ошибка обработки, заказ не найден');
        }
        $sig = $freedomApi->makeSign(last(request()->segments()), $params);
        if ($sig != $params['pg_sig']) {
            Log::error('freedom_wrong_signature '.$params['pg_sig']);
            return $freedomApi->responseXML('result','rejected','Неверная подпись');
        }
        if ($request->pg_result == 1) {
            Log::error('order success '.$order->id);
            $sum = $params['pg_ps_full_amount'] ?? $params['pg_amount'];
            $order->successfullyPaid($sum, true, $request->pg_payment_date, $request->pg_payment_id);
            return $freedomApi->responseXML('result','ok','Платеж обработан');
        }
        return $freedomApi->responseXML('result','rejected','Ошибка обработки, заказ не найден');
    }

}
