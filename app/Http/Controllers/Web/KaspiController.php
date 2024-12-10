<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Specific\Order;
use App\Models\Types\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class KaspiController extends Controller {

    private $urlsAllowed = [
        '37.99.46.150',
        '37.99.47.133',
        '194.187.247.152',
        '115.31.179.246'
    ];
    
    private $kaspiErrors = [
        0 => 'успешно',
        1 => 'заказ не найден',
        2 => 'заказ отменен',
        3 => 'заказ уже оплачен',
        4 => 'платеж в обработке',
        5 => 'другая ошибка',
        6 => 'сумма оплаты отличается от суммы заказа',
    ];

    public function __construct() {
        $this->middleware(function ($request, $next) {
            $ip = $request->ip();
            if(!in_array($ip, $this->urlsAllowed)) {
                return response()->json('404');
            }
            return $next($request);
        })->only('webhook');
    }

    public function webhook(Request $request) {

        Log::error('kaspi webhook');
        Log::error($request->all());

        $validator = Validator::make($request->all(), [
            'command' => ['required', 'in:pay,check'],
            'txn_id'  => ['required'],
            'account' => ['required'],
            'sum'     => Rule::requiredIf($request->command === 'pay'),
        ]);

        $responseData = $request->only('command', 'txn_id', 'txn_date', 'account', 'sum');

        if(!$validator->passes()) {
            $responseData['result_code'] = 5;
            return $this->kaspiResponse($responseData);
        }
        
        $order = Order::withTrashed()->find($responseData['account']);
        
        if(!$order) {
            $responseData['result_code'] = 1;
            return $this->kaspiResponse($responseData);
        }
        
        $responseData['userName'] = $order->name;
        $responseData['showName'] = $order->timetable && $order->timetable->show ? $order->timetable->show->title : '';
        
        if($order->trashed()) {
            $responseData['result_code'] = 2;
            return $this->kaspiResponse($responseData);
        }
        
        if($order->paid) {
            $responseData['result_code'] = 3;
            return $this->kaspiResponse($responseData);
        }
        
        switch($request->command) {
            case 'check':
                $responseData = $this->checkOrder($order, $responseData);
                break;
            case 'pay':
                $responseData = $this->payOrder($order, $responseData);
                break;
        }
        return $this->kaspiResponse($responseData);
    }
    

    private function checkOrder(Order $order, $data) {
        $order->prolong();
        $data['result_code'] = 0;
        $data['sum'] = $order->price;
        return $data;
    }

    
    private function payOrder(Order $order, $data) {
        if((float)$data['sum'] != $order->price) {
            $data['result_code'] = 6;
            $data['sum'] = $order->price;
            return $data;
        }
        $order->pay_system = PaymentType::KASPI;
        $order->save();
        $res = $order->successfullyPaid($order->price, true, $this->kaspiDateFormatToDbFormat($data['txn_date']), $data['txn_id']);
        $data['sum'] = $order->price;
        $data['result_code'] = $res ? 0 : 5;
        $data['prv_txn'] = $order->id;
        return $data;
    }
    

    private function kaspiResponse($data) {
        $view = 'api.kaspi-response';
        $data['comment'] = $this->kaspiErrors[$data['result_code']] ?? '';
        $xml = view($view, $data)->render();
        return response($xml, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
    
    
    private function kaspiDateFormatToDbFormat($txn_date) {
        return date('Y-m-d H:i:s', strtotime($txn_date));
    }
    
    public function kaspiTest() {
        $order = Order::find(10013895);
        return view('widget.kaspi-form', compact('order'));
    }


}
