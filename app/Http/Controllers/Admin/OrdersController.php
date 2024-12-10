<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specific\OrganizerShow;
use App\Models\Specific\RefundApplication;
use App\Models\Specific\Timetable;
use Illuminate\Validation\ValidationException;

class OrdersController extends Controller {

    public function approveRefundApp($id) {
        $application = RefundApplication::findOrFail($id);
        return response()->json($application->refund());
    }

    public function scansData($timetable_id) {
        $timetable = Timetable::find($timetable_id);
        if(!$timetable) {
            return response()->json([]);
        }
        return response()->json([
            'timetable'         => $timetable,
            'ticketsSold'       => $timetable->soldTickets,
            'ticketsScanned'    => $timetable->ticketsScanned,
            'scansMade'         => $timetable->totalScans
        ]);
    }

    public function changeOrganizerShowStatus($id, $status) {
        $show = OrganizerShow::where([
            'id' => $id,
            'organizer_add_status' => 'new'
        ])->first();
        if($show) {
            if($status === 'approved') {
                if(!$show->venue_id) {
                    throw ValidationException::withMessages(['Не указана площадка события']);
                }
                foreach($show->timetables as $timetable) {
                    if(!$timetable->venue_id || !$timetable->venue_scheme_id) {
                        throw ValidationException::withMessages(['Не указана площадка сеанса']);
                    }
                }
            }
            $show->organizer_add_status = $status;
            $show->save();
        }
        return response()->json(true);
    }

//    public function orderShow($order_id) {
//        $order = $this->orderForPage($order_id);
//        return json_encode($order);
//    }
//
//    public function orderForPage($order_id) {
//        $order = Order::with('orderItems')->with('orderItems.product')->with('bonusTransactions')->find($order_id);
//        $order->sum_before_general_discount = $order->sum_before_general_discount();
//        $order->sum_to_pay = $order->getSumToPay();
//        return $order;
//    }
//
//    public function mailSend($id, Request $request) {
//        $order = Order::find($id);
//        if(env('SEND_EMAILS') ==  1) {
//            Mail::send(new ClientOrderMade($order,app()->getLocale()));
//        }
//    }
//
//    public function approve($order_id, Request $request) {
//        $order = Order::findOrFail($order_id);
//        $amount = $order->getSumToPay();
////        $approval_code = $order->approval_code;
//        $pay_id = $order->pay_id;
//        $to_confirm = min($amount, $order->pay_summ);
////        $response = CloudPaymentAPI::confirmPayment($pay_id, $to_confirm);
////        Log::error("::: CLOUD APPROVE :::");
////        Log::error((array)$response);
////        if($response->Success) {
////            if($amount > $to_confirm) {
////                $response = CloudPaymentAPI::tokenPayment($order, ($amount - $to_confirm));
////                Log::error("::: CLOUD TOKEN PAY :::");
////                Log::error((array)$response);
////                if($response->Success) {
////                    $order->paidSuccessfully($pay_id, $amount);
////                    return json_encode(['success' => 1, 'order' => $this->orderForPage($order_id)]);
////                } else {
////                    return json_encode(['success' => 0]);
////                }
////            } else {
////                $order->paidSuccessfully($pay_id, $amount);
////                return json_encode(['success' => 1, 'order' => $this->orderForPage($order_id)]);
////            }
////        } else {
////            return json_encode(['success' => 0]);
////        }
//        if($amount > $to_confirm) {
//            $response = CloudPaymentAPI::tokenPayment($order, ($amount - $to_confirm));
//            Log::error("::: CLOUD TOKEN PAY :::");
//            Log::error((array)$response);
//            if(!$response->Success) {
//                return json_encode(['success' => 0]);
//            }
//            $order->update([
//                'pay_id_add' => $response->Model->TransactionId,
//                'pay_summ_add' => $response->Model->Amount,
//            ]);
//            $response = CloudPaymentAPI::confirmPayment($pay_id, $to_confirm);
//            Log::error("::: CLOUD APPROVE :::");
//            Log::error((array)$response);
//            if(!$response->Success) {
//                CloudPaymentAPI::refundPayment($order->pay_id_add, $order->pay_summ_add);
//                return json_encode(['success' => 0]);
//            }
//            $order->paidSuccessfully($pay_id, $to_confirm);
//            return json_encode(['success' => 1, 'order' => $this->orderForPage($order_id)]);
//
//        } else {
//            $response = CloudPaymentAPI::confirmPayment($pay_id, $to_confirm);
//            Log::error("::: CLOUD APPROVE :::");
//            Log::error((array)$response);
//            if(!$response->Success) {
//                return json_encode(['success' => 0]);
//            }
//            $order->paidSuccessfully($pay_id, $amount);
//            return json_encode(['success' => 1, 'order' => $this->orderForPage($order_id)]);
//        }
//    }
//
//
//    public function cancel($order_id, Request $request) {
//        $order = Order::findOrFail($order_id);
//        if($order->pay_method == 'card' && $order->status == 'authorized') {
//            $pay_id = $order->pay_id;
//            $response = CloudPaymentAPI::cancelPayment($pay_id);
//            Log::error("::: CLOUD CANCEL :::");
//            Log::error((array)$response);
//            if($response->Success) {
//                $order->orderCancelled();
//                return json_encode(['success' => 1, 'order' => $this->orderForPage($order_id)]);
//            } else {
//                return json_encode(['success' => 0]);
//            }
//        } else {
//            $order->orderCancelled();
//            return json_encode(['success' => 1, 'order' => $this->orderForPage($order_id)]);
//        }
//    }
//
//
//    public function update(Request $request, $id)
//    {
//        $order = Order::find($id);
//
//        $old_status = $order->status;
//        $new_status = $request->status;
//        $type_of_operation = 'unchanged';
//        if($old_status == 'cancelled' && $new_status != 'cancelled') {
//            $type_of_operation = 'decrement';
//        }
//        if($old_status != 'cancelled' && $new_status == 'cancelled') {
//            $type_of_operation = 'increment';
//        }
//
//        $input = $request->all();
//        $order->update($input);
//        $order->orderAffectQuantities($type_of_operation);
//
//        return redirect('admin/orders')->with('message', __("Operation successfully completed"));
//    }
//
//
//    public function transferPayment($order_id, Request $request) {
//        $order = Order::findOrFail($order_id);
//        $order->paidSuccessfully();
//        return json_encode(['success' => 1, 'order' => $this->orderForPage($order_id)]);
////        return back()->with('message', __("Operation successfully completed"));
//    }
//
//
//    public function returnOrder($order_id, Request $request) {
//        $order = Order::findOrFail($order_id);
//        if($order->pay_method == 'card' && $order->status == 'paid') {
//            $sum_to_return = $request->sum;
//            $pay_id = $order->pay_id;
//            $response = CloudPaymentAPI::refundPayment($pay_id, min($sum_to_return, $order->pay_summ));
//            Log::error("::: CLOUD REFUND :::");
//            Log::error((array)$response);
//            if(!$response->Success) {
//                return json_encode(['success' => 0]);
//            }
//            $sum_to_return = $sum_to_return - $order->pay_summ;
//            if($order->pay_id_add && $order->pay_summ_add && $sum_to_return > 0) {
//                $response2 = CloudPaymentAPI::refundPayment($order->pay_id_add, min($sum_to_return, $order->pay_summ_add));
//                if(!$response2->Success) {
//                    return json_encode(['success' => 0]);
//                }
//            }
//            $order->orderCancelled();
//            return json_encode(['success' => 1, 'order' => $this->orderForPage($order_id)]);
//        }
//        $order->orderCancelled();
//        return json_encode(['success' => 1, 'order' => $this->orderForPage($order_id)]);
////        return back()->with('message', __("Operation successfully completed"));
//    }
//    
//
//    public function report($id) {
//        $c = Order::findOrFail($id);
//        $certificate = new PDFOrder($c);
//        return $certificate->stream();
//    }




}
