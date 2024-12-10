<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\API\JysanAPI;
use App\Models\Specific\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JysanController extends Controller {
    
    public function callback(Request $request) {
        Log::error('callback from jysan');
        Log::error($request->all());
        $order = Order::find($request->order);
        if(!$order) return 'No order';
        $api = new JysanAPI();
        $statusObj = $api->getOrderStatus($order);
        if($statusObj->code == 0 && $statusObj->operation->rc == '00') {
            Log::error('success');
            $order->successfullyPaid($statusObj->operation->amount, true, null, $statusObj->operation->rrn);
        }
        if(isset($statusObj->res_desc) && $statusObj->res_desc) {
            $order->update(['pay_comment' => $statusObj->res_desc]);
        }
        if(isset($statusObj->description) && $statusObj->description) {
            $order->update(['pay_comment' => $statusObj->description]);
        }
        if(isset($statusObj->operation) && isset($statusObj->operation->description) && $statusObj->operation->description) {
            $order->update(['pay_comment' => $statusObj->operation->description]);
        }
        if ($request->isMethod('get')) {
            return redirect('/kz/payment/'.$order->id.'/'.$order->hash);
        } 
        return 'Ok';
    }
    
    public function payment($id, $hash) {
        $order = Order::where('id', $id)->where('hash', $hash)->first();
        if(!$order) abort(404);
        return view('widget.payment', compact('order')); 
    }

   
}
