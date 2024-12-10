<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Specific\Order;
use App\Models\Specific\Show;
use App\Models\Specific\Ticket;
use App\Models\Specific\Timetable;
use App\Models\Specific\VenueScheme;
use App\Resources\Partner\PartnerOrderResource;
use App\Resources\Partner\PartnerSchemeResource;
use App\Resources\Partner\PartnerSectionInfoResource;
use App\Resources\Partner\PartnerTimetablesResource;
use App\Traits\APIResponseTrait;
use Illuminate\Http\Request;

/**
 * @OA\PathItem(path="/partner")
 */

class APIPartnerController extends Controller {

    use APIResponseTrait;

    /**
     * @OA\Get(
     *     path="/partner/shows",
     *     summary="Получение списка событий",
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Timetable"))
     *     )
     * )
     */
    public function getTimetables(Request $request) {
        $user = $request->user;
        $showIds = $user->shows()
            ->local()
            ->pluck('id')
            ->toArray();
        $timetables = Timetable::future()
            ->local()
            ->visibleTill()
            ->whereIn('show_id', $showIds)
            ->whereHas('show', function ($query) {
                $query->active();
            })
            ->with('show')
            ->with('show.venue')
            ->with('show.venue.city')
            ->orderBy('date','asc')
            ->get();
        return response()->json(PartnerTimetablesResource::collection($timetables));
    }

    /**
     * @OA\Get(
     *     path="/partner/shows/{id}",
     *     summary="Детали по выбранному событию",
     *     description="Выдает количество доступных для продажи билетов по каждому сектору / ценовой группе",
     *     @OA\Parameter(
     *         description="Id события",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="tickets", type="array", @OA\Items(ref="#/components/schemas/SectionInfo")),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Error", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/Error"))),
     *     @OA\Response(response=404, description="Error", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/NotFoundError")))
     * )
     */
    public function getTimetableDetails($id, Request $request) {
        $timetable = $request->timetable;
        $tickets = $timetable->groupedCountTickets();
        return response()->json([
            'tickets' => PartnerSectionInfoResource::collection($tickets)
        ]);
    }

    /**
     * @OA\Get(
     *     path="/partner/shows/{id}/section/{sectionId}",
     *     summary="Билеты по выбранному сектору",
     *     description="Выдает список доступных для продажи билетов по сектору – билеты могут быть по местам (параметры row и seat имеют значения) или входные для сектора со свободной рассадкой (параметры row и seat равны null)",
     *     @OA\Parameter(
     *         description="Id события",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         description="Id сектора или ценовой группы для событий по ценовым группам",
     *         in="path",
     *         name="sectionId",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="tickets", type="array", @OA\Items(ref="#/components/schemas/Ticket")),
     *              @OA\Property(property="seats", type="array", @OA\Items(ref="#/components/schemas/Seat")),
     *              @OA\Property(property="prices", type="array", @OA\Items(type="integer")),
     *              @OA\Property(property="type", type="string", example="seats", description="pricegroups для ценовых групп, enter - для сектора с входными без мест (например, фан зона), seats - для сектора по местам, "),
     *         )
     *     ),
     *     @OA\Response(response=400, description="Error", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/Error"))),
     *     @OA\Response(response=404, description="Error", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/NotFoundError")))
     * )
     */
    public function getSectionDetails(Request $request, $id, $sectionId = null) {
        $timetable = $request->timetable;
        return response()->json($timetable->getTicketsForGroup($sectionId));
    }

    /**
     * @OA\Post(
     *     path="/partner/shows/{id}/order",
     *     summary="Инициирование заказа",
     *     description="Генерация заказа на этапе, когда клиент выбрал билеты и перешел к процедуре оплаты, но еще не оплатил. Бронь на выбранные билеты держится 30 минут. В случае успешного запроса ответом приходит id созданного заказа. Если оплаты не происходит, через указанный интервал времени заказ удаляется, и билеты высвобождаются для продажи",
     *     @OA\Parameter(
     *         description="Id события",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                   @OA\Property(property="cart", type="array", @OA\Items(ref="#/components/schemas/CartItem")),
     *              )
     *         )
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/Order"))),
     *     @OA\Response(response=400, description="Error", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/Error"))),
     *     @OA\Response(response=404, description="Error", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/NotFoundError")))
     * )
     */
    public function initiateOrder($id, Request $request) {
        $timetable = $request->timetable;
        if(!$timetable->active) {
            return response()->json(['error' => 'Мероприятие недоступно'], 400);
        }
        if(!Ticket::allTicketsAvailable($request->cart, $timetable)) {
            return response()->json(['error' => 'К сожалению, некоторые билеты были уже выкуплены'], 400);
        }
        $user = $request->user;
        $order = Order::generateOrderFromRequest($timetable, null, $request, $user);
        if(!$order) {
            return response()->json(['error' => 'Что-то пошло не так, заказ не был создан']);
        }
        return response()->json((new PartnerOrderResource($order)));
    }

    /**
     * @OA\Get(
     *     path="/partner/order/{id}",
     *     summary="Получение статуса заказа",
     *     @OA\Parameter(
     *         description="Id заказа",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/Order"))),
     *     @OA\Response(response=404, description="Error", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/NotFoundError")))
     * )
     */
    public function getOrderDetails($id, Request $request) {
        $order = $request->order;
        $order = new PartnerOrderResource($order);
        return response()->json($order);
    }

    /**
     * @OA\Post(
     *     path="/partner/order/{id}/confirm",
     *     summary="Подтверждение оплаты заказа",
     *     description="Вызывается, когда оплата получена, и билеты необходимо выкупить",
     *     @OA\Parameter(
     *         description="Id заказа",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/Order"))),
     *     @OA\Response(response=400, description="Error", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/Error"))),
     *     @OA\Response(response=404, description="Error", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/NotFoundError")))
     * )
     */
    public function confirmOrder($id, Request $request) {
        $order = $request->order;
        if($order->isExpired()) {
            return response()->json(['error' => 'Заказ истек'], 404);
        }
        if($order->paid) {
            return response()->json(['error' => "Заказ уже оплачен"], 400);
        }
        $order->update(['pay_system' => 'partner']);
        $order->successfullyPaid($order->price, false);
        $order = new PartnerOrderResource($order);
        return response()->json($order);
    }

    /**
     * @OA\Delete(
     *     path="/partner/order/{id}/cancel",
     *     summary="Отмена заказа",
     *     description="Применимо только к неоплаченному заказу",
     *     @OA\Parameter(
     *         description="Id заказа",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\MediaType(mediaType="application/json", @OA\Schema(type="boolean"))),
     *     @OA\Response(response=400, description="Error", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/Error"))),
     *     @OA\Response(response=404, description="Error", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/NotFoundError")))
     * )
     */
    public function cancelOrder($id, Request $request) {
        $order = $request->order;
        if($order->paid) {
            return response()->json(['error' => 'Заказ уже оплачен'], 400);
        }
        $order->delete();
        return response()->json(true);
    }

    /**
     * @OA\Delete(
     *     path="/partner/order/{id}/refund",
     *     summary="Возврат заказа",
     *     description="Применимо только к оплаченному заказу. Заказ попадает в статистику возвратов",
     *     @OA\Parameter(
     *         description="Id заказа",
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(response=200, description="OK", @OA\MediaType(mediaType="application/json", @OA\Schema(type="boolean"))),
     *     @OA\Response(response=400, description="Error", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/Error"))),
     *     @OA\Response(response=404, description="Error", @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/NotFoundError")))
     * )
     */
    public function refundOrder($id, Request $request) {
        $order = $request->order;
        if(!$order->paid) {
            return response()->json(['error' => 'Заказ не оплачен'], 400);
        }
        $order->refund();
        return response()->json(true);
    }

    public function getVenueSchemeData($venueSchemeId) {
        $venueScheme = VenueScheme::find($venueSchemeId);
        $venueScheme->load('sections');
        return response()->json(new PartnerSchemeResource($venueScheme));
    }

    public function getVenueSchemeSvg($venueSchemeId) {
        $venueScheme = VenueScheme::find($venueSchemeId);
        $venueScheme->load('sections');
        return view('api.venue-svg', compact('venueScheme'));
    }
}
