<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Specific\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use jetpay\Gate;

class PaymentController extends Controller {

    public function callback(Request $request) {
        Log::error('callback from jetpay');
        Log::error($request->all());
        $data = $request->all();
//        if(isset($data['payment']) && isset($data['payment']['id']) && in_array($data['payment']['id'], [10017245, 10017424,10017859, 10014151])) {
//            Log::error('found problematic order');
//            return response('Ok', 200);
//        }
        $gate = new Gate(env('JETPAY_SECRET'));
        $callback = $gate->handleCallback(json_encode($data));
        $paymentStatus = $callback->getPaymentStatus();
        $orderId = $callback->getPaymentId();
        Log::error('order id '.$orderId);
        Log::error('payment status '.$paymentStatus);
        if($paymentStatus === 'success') {
            $order = Order::find($orderId);
            if($order) $order->successfullyPaid($request->payment['sum']['amount'] / 100, true, null, $request->operation['request_id']);
        }
        if($paymentStatus === 'refunded') {
            $order = Order::find($orderId);
            if($order && ($request->operation['sum_initial']['amount'] / 100) == $order->price) {
                $order->refund();
            }
        }
        if($paymentStatus === 'awaiting 3ds result') {
            $order = Order::find($orderId);
            $order->prolong();
        }
        return response('Ok', 200);
    }

    public function paymentResultRedirect($orderId, $orderHash, $result) {
        $order = Order::findByIdAndHash($orderId, $orderHash);
        if(!$order) abort(404);
        $timetable = $order->timetable;
        $show = $timetable ? $timetable->show : null;
        return view('widget.redirectPage', compact('order','result', 'timetable', 'show'));
    }
    
    public function kaspiPage($orderId, $orderHash) {
        $order = Order::findByIdAndHash($orderId, $orderHash);
        if(!$order) abort(404);
        return view('widget.kaspi-instr', compact('order'));
    }

}
