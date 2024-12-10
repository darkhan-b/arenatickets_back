<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Models\Reports\SalesListReport;
use App\Models\Specific\Order;
use App\Models\Specific\RefundApplication;
use App\Models\Specific\Show;
use App\Models\Specific\Ticket;
use App\Models\Specific\Timetable;
use App\Traits\APIResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Box\Spout\Common\Type;

class DashboardController extends Controller
{
    use APIResponseTrait;

    public function dashboard(Request $request) {
        $clientId = $request->clientId ?? null;
        $ordersTodayQ = Order::paid()->notInvitation()->whereDate('created_at', Carbon::today());
        $ordersSumTodayQ = Order::paid()->notInvitation()->whereDate('created_at', Carbon::today());
        $ordersLastMonthQ = Order::paid()->notInvitation()->where('created_at', '>=', Carbon::now()->subDays(30)->toDateTimeString());
        $ordersSumLastMonthQ = Order::paid()->notInvitation()->where('created_at', '>=', Carbon::now()->subDays(30)->toDateTimeString());
        $showsActiveQ = Show::showable();
        $ticketsInSaleQ = Ticket::available()->whereIn('timetable_id', Timetable::future()->whereHas('show', function($q) { $q->active(); })->pluck('id')->toArray());
//        $refundApplicationsQ = RefundApplication::whereNull('refunded_at');
        if($clientId) {
            $ordersTodayQ->where('client_id', $clientId);
            $ordersSumTodayQ->where('client_id', $clientId);
            $ordersLastMonthQ->where('client_id', $clientId);
            $ordersSumLastMonthQ->where('client_id', $clientId);
            $showsActiveQ->where('client_id', $clientId);
            $ticketsInSaleQ->whereHas('timetable', function($q) use($clientId) {
                $q->where('client_id', $clientId);
            });
//            $refundApplicationsQ->whereHas('order', function($q) use($clientId) {
//                $q->where('client_id', $clientId);
//            });
        }
        return $this->apiSuccess([
            ['title' => 'Заказов за сегодня', 'url' => '', 'value' => $ordersTodayQ->count()],
            ['title' => 'Сумма продаж за сегодня', 'url' => '', 'value' => $ordersSumTodayQ->sum('price')],
            ['title' => 'Заказов за последний месяц', 'url' => '', 'value' => $ordersLastMonthQ->count()],
            ['title' => 'Сумма продаж за последний месяц', 'url' => '', 'value' => (int)$ordersSumLastMonthQ->sum('price')],
            ['title' => 'Событий активно', 'url' => '', 'value' => $showsActiveQ->count()],
            ['title' => 'Билетов в продаже', 'url' => '', 'value' => $ticketsInSaleQ->count()],
//            ['title' => 'Заявки на возврат', 'url' => '', 'value' => $refundApplicationsQ->count()],
        ]);
    }

    public function salesExcel(ReportRequest $request) {
        $data = $request->only(
            'show_ids',
            'timetable_ids',
            'venue_ids',
            'organizer_ids',
            'client_ids',
            'category_ids',
            'date_from',
            'date_to',
            'report_type'
        );
        if(isset($data['date_from']) && $data['date_from']) $data['date_from'] = mb_substr($data['date_from'], 0, 10);
        if(isset($data['date_to']) && $data['date_to']) $data['date_to'] = mb_substr($data['date_to'], 0, 10);
        $report = new SalesListReport($data);
        $report->generate();
        if($report->hasData()) return $report->toExcel();
        return null;
    }




}
