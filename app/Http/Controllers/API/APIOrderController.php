<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderFillRequest;
use App\Http\Requests\OrderInitRequest;
use App\Mail\ConfirmEmailMail;
use App\Models\API\FreedomPayAPI;
use App\Models\API\KaspiAPI;
use App\Models\Specific\Order;
use App\Models\Specific\Promocode;
use App\Models\Specific\PromocodeTry;
use App\Models\Types\PaymentType;
use Illuminate\Support\Facades\Mail;
use App\Models\Specific\OrderItem;
use App\Models\Specific\Ticket;
use App\Models\Specific\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class APIOrderController extends Controller {


    public function getOrder($id, $hash, Request $request) {
        $order = Order::where('id',$id)
            ->with([
                'timetable',
                'timetable.show',
                'timetable.show.category',
                'timetable.show.showTerm',
                'timetable.show.showTerm.showTermPoints',
				'timetable.sectionsWithoutSeatSelections',
                'orderItems:id,order_id,timetable_id,section_id,pricegroup_id,ticket_id,row,seat,seat_id,vendor_seat_id,price,original_price',
                'orderItems.section',
                'orderItems.pricegroup'
            ])
            ->where('hash',$hash)
            ->first();
        if(!$order) {
            abort(404);
        }
        $order->timetable->append('hasPromocodes');
        return response()->json([
            'order'        => $order,
            'secondsLeft'  => $order->secondsToExpiry,
            'user'         => auth('sanctum')->user()
        ]);
    }


    public function initOrder(OrderInitRequest $request) {

        $timetable = Timetable::findOrFail($request->timetable_id);

        if(!$timetable->active && !in_array($timetable->id, TIMETABLES_FOR_TESTING)) {
            return response()->json(['error' => 'Мероприятие недоступно']);
        }

		if($timetable->sell_till && $timetable->salesFinished) {
			return response()->json(['error' => 'Продажи по мероприятию завершены']);
		}

		if(!$timetable->sell_till && $timetable->passed) {
			return response()->json(['error' => 'Мероприятие прошло']);
		}

        if(!Ticket::allTicketsAvailable($request->cart, $timetable)) {
            return response()->json(['error' => 'К сожалению, некоторые билеты были уже выкуплены']);
        }

        $user = auth('sanctum')->user();

		try {
			$order = Order::generateOrderFromRequest($timetable, $user, $request);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()]);
		}

        if(!$order) {
            return response()->json(['error' => 'Что-то пошло не так, заказ не был создан']);
        }

        return response()->json([
            'order' => $order,
            'user'  => $user
        ]);
    }

    public function initForum(ForumInitRequest $request) {

        $timetable = Timetable::findOrFail($request->timetable_id);

        if(!$timetable->active && !in_array($timetable->id, TIMETABLES_FOR_TESTING)) {
            return response()->json(['error' => 'Мероприятие недоступно']);
        }

		if($timetable->sell_till && $timetable->salesFinished) {
			return response()->json(['error' => 'Продажи по мероприятию завершены']);
		}

		if(!$timetable->sell_till && $timetable->passed) {
			return response()->json(['error' => 'Мероприятие прошло']);
		}

        if(!Ticket::allTicketsAvailable($request->cart, $timetable)) {
            return response()->json(['error' => 'К сожалению, некоторые билеты были уже выкуплены']);
        }

        $user = auth('sanctum')->user();

		try {
			$order = Order::generateOrderFromRequest($timetable, $user, $request);
		} catch (\Exception $e) {
			return response()->json(['error' => $e->getMessage()]);
		}

        if(!$order) {
            return response()->json(['error' => 'Что-то пошло не так, заказ не был создан']);
        }

        return response()->json([
            'order' => $order,
            'user'  => $user
        ]);
    }


    public function fillOrder(OrderFillRequest $request, $id, $hash) {

        $user = auth('sanctum')->user();

        $order = Order::where('id',$id)
            ->with('orderItems')
            ->where('hash',$hash)
            ->first();

        if(!$order || $order->isExpired()) abort(404);

        if($order->paid) {
            return response()->json(['error' => "Заказ уже оплачен"]);
        }

        $show = $order->show;

        $pay_system = $request->pay_system;
        $hide_price = 0;
        if($pay_system === 'invitation_hide') {
            $pay_system = PaymentType::INVITATION;
            $hide_price = 1;
        }

        $comment = $user && $user->can('write_order_comments') ? $request->comment : null;
        $pay_system_imitated = $user && $user->can('imitate_pay_methods') ? $request->pay_system_imitated : null;
        $show_to_organizer = true;
        
        if($pay_system === PaymentType::INVITATION) {
            $show_to_organizer = false;
            if($user && $user->can('hide_orders_for_organizers') && $request->show_to_organizer) {
                $show_to_organizer = true;
            }
        }

        $orderData = [
            'name'               => $request->name,
            'email'              => $request->email,
            'phone'              => $request->phone,
            'comment'            => $request->comment,
            'pay_system'         => $request->pay_system,
            'pay_system_imitated'=> $request->pay_system_imitated,
            'show_to_organizer'  => $request->show_to_organizer,
            'hide_price'         => $hide_price,
            'is_refundable'      => $request->is_refundable ?? 1,
            'company'            => $request->company,
            'position'           => $request->position,
            'country'            => $request->country,
            'participation'      => $request->participation,
            'source'             => $request->source,
        ];
                
        $order->update($orderData);
        

        $order->applyPromocode(isset($request->promocode) && $request->promocode ? $request->promocode : null);

        $order->recountPrice(); // if promocode was used or refundable was changed

        if($user) {
            $user->update([
                'name'  => $request->name,
                'phone' => $request->phone,
            ]);
        }

        if(in_array($pay_system, [PaymentType::CASH]) && $user && $user->can('kassa')) {
            $order->successfullyPaid($order->price, false);
            return response()->json(['success' => 1, 'redirect' => $order->ticketsLink ]);
        }

        if($pay_system == PaymentType::KASPI) {
//            return response()->json(['error' => "Оплата kaspi временно не доступна"]);
            $qr = !$request->mobile ? KaspiAPI::qr($order) : null;
            return response()->json([
                'success'  => 1,
                'qr'       => $qr,
                'redirect' => env('APP_URL').'/payment/'.$order->id.'/'.$order->hash.'/kaspi'
            ]);
        }

        if(in_array($pay_system, [PaymentType::INVITATION]) && $show && $user && $user->isOrganizerForShow($show->id)) {
            $order->soldAsInvitation();
            return response()->json(['success' => 1, 'redirect' => $order->ticketsLink ]);
        }

        if ($pay_system === PaymentType::FORUM) {
            $order->soldAsForum();
            return response()->json([
                'success' => 1,
                'redirect' => $order->ticketsLink,
                'user' => [
                    'id' => $user->id ?? null,
                    'name' => $user->name ?? null,
                    'email' => $user->email ?? null,
                    'phone' => $user->phone ?? null,
                ],
            ]);
        }

        if($pay_system == PaymentType::CARD) {
            $freedomApi = new FreedomPayAPI($order->legal_entity_id);
            $res = $freedomApi->initiateTransaction($order);
            if ($res['status'] == 200) {
                return response()->json(['success' => 1, 'redirect' => $res['data']]);
            }
        }

        return response()->json(['success' => 0, 'user' => $user]);
    }

    public function cancelOrder(Request $request, $id, $hash) {
        $order = Order::where('id',$id)
            ->with('orderItems')
            ->where('hash',$hash)
            ->first();
        if($order && !$order->paid && $order->available_for_manual_delete) {
            $order->delete('user');
        }
        return response()->json(true);
    }

    public function deleteOrderItem(Request $request, $id, $hash, $itemId) {
        $order = Order::findByIdAndHash($id, $hash);
        if($order && !$order->paid) {
            $orderItem = OrderItem::where([
                'order_id' => $id,
                'id' => $itemId
            ])->first();
            if($orderItem) {
                $orderItem->delete();
                $order->recountPrice();
            }
        }
        return response()->json(true);
    }

    public function confirmEmailGenerate(Request $request, $id, $hash) {
        $request->validate([
            'email' => ['required', 'email']
        ]);
        $order = Order::findByIdAndHash($id, $hash);
        if(!$order) abort(404);
        $code = $order->generateEmailConfirmationCode();
        Mail::send(new ConfirmEmailMail($request->email, $code));
        return response()->json(true);
    }

    public function confirmEmailCheck(Request $request, $id, $hash) {
        $request->validate([
            'code' => ['required']
        ]);
        $code = $request->code;
        $order = Order::findByIdAndHash($id, $hash);
        if(!$order) abort(404);
        if(!$order->email_code) $order->generateEmailConfirmationCode();
        return response()->json($order->email_code == $code);
    }

    public function promocodeCheck(Request $request) {
        $request->validate([
            'timetable_id' => 'required|numeric',
            'order_id'     => 'required|numeric',
            'promocode'    => 'required|string|max:100|min:1'
        ]);
        $promocode = mb_strtolower($request->promocode);
        $timetable_id = $request->timetable_id;
        $ip = $request->ip();
        if(PromocodeTry::where('created_at', '>', date('Y-m-d H:i:s', strtotime('-1 minute')))->where('ip', $ip)->count() > 2) {
            return response()->json('Превышено количество проверок промокода', 400);
        }
        PromocodeTry::create(['ip' => $ip]);
        $promocode = Promocode::where(compact('promocode', 'timetable_id'))->first();
        $order = Order::find($request->order_id);
        if($promocode && !$promocode->canBeUsed($order)) {
            $promocode = null;
        }
        return response()->json($promocode ? $promocode->discount : 0);
    }

}
