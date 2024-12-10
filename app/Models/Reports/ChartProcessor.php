<?php

namespace App\Models\Reports;

use App\Models\Specific\Order;
use App\Models\Specific\OrderItem;
use App\Models\Specific\Timetable;
use Illuminate\Support\Facades\DB;

class ChartProcessor {

    public $user;
    public $from;
    public $to;
    public $categoryId;
    public $paySystems;
    public $timetableIds;

    public function __construct($user, $from, $to, $timetableIds = null, $paySystems = null, $categoryId = null) {
        $this->user = $user;
        $this->from = $from;
        $this->to = $to;
        $this->timetableIds = $timetableIds;
        $this->categoryId = $categoryId;
        $this->paySystems = $paySystems;
    }

    public function getSalesByDaysData() {
        $data = $this->initialOrderQuery()
            ->selectRaw('DATE(orders.created_at) as date, COUNT(*) as cnt, SUM(orders.pay_sum) as paysum')
            ->groupBy('date')
            ->get()
            ->keyBy('date')
            ->all();
        $orderCntData = $this->initialOrderItemsQuery()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->selectRaw('DATE(orders.created_at) as date, COUNT(*) as cnt')
            ->groupBy('date')
            ->get()
            ->keyBy('date')
            ->all();
        $begin = new \DateTime($this->from);
        $end = new \DateTime($this->to);
        $end->modify('+ 1 day');
        $interval = \DateInterval::createFromDateString('1 day');
        $period = new \DatePeriod($begin, $interval, $end);
        $res = [];

        foreach ($period as $dt) {
            $d = $dt->format("Y-m-d");
            $res[] = isset($data[$d]) ? [
                'date' => $d,
                'cnt' => $orderCntData[$d]->cnt ?? 0,
                'paysum' => $data[$d]['paysum']
            ] : ['date' => $d, 'cnt' => 0, 'paysum' => 0];
        }
        return $res;
    }

    public function getTopEventsData() {
        $data = $this->initialOrderQuery()
            ->selectRaw('timetable_id, COUNT(*) as cnt, SUM(pay_sum) as paysum')
            ->groupBy('timetable_id')
            ->orderBy('paysum', 'desc')
            ->with(['timetable:id,show_id', 'timetable.show:id,title'])
            ->take(5)
//            ->with('timetable.show:id,title')
            ->get();
        $d = 0;
        $data = $data->map(function($v) use(&$d) {
            $d += $v->paysum;
            return [
                'label' => $v->timetable->show->title ?? '-',
                'value' => $v->paysum
            ];
        });
        $total = $this->initialOrderQuery()->sum('pay_sum');
        if($total > $d) {
            $data[] = [
                'label' => __('rest'),
                'value' => $total - $d,
            ];
        }

        return $data;
    }

    public function getByCategoriesData() {
        $data = $this->initialOrderQuery()
            ->join('timetables', 'orders.timetable_id', '=', 'timetables.id')
            ->join('shows', 'timetables.show_id', '=', 'shows.id')
            ->join('show_category', 'shows.id', '=', 'show_category.show_id')
            ->join('categories', 'show_category.category_id', '=', 'categories.id')
            ->groupBy('categories.id')
//            ->orderBy('paysum', 'asc')
            ->selectRaw('categories.id, categories.title, SUM(orders.pay_sum) as paysum')
            ->orderByRaw('paysum desc')
            ->get();
        return $data;
    }

    public function getByPayTypesData() {
        $data = $this->initialOrderQuery()
            ->selectRaw('pay_system, COUNT(*) as cnt, SUM(pay_sum) as paysum')
            ->groupBy('pay_system')
            ->get();
        return $data;
    }

    public function getTimetables() {
        $q = Timetable::query();
        $q->userHasAccessToData($this->user);
        $q->with('show:id,title')
            ->whereHas('show')
            ->select('id', 'show_id', 'date');
        $data = $q->get();
        return $data->map(function($v) {
            return [
                'id' => $v->id,
                'title' => ($v->show->title ?? '-').' ('.$v->dateString.')'
            ];
        });
    }

    public function initialOrderQuery() {
        $q = Order::whereBetween('orders.created_at', [$this->from.' 00:00:00', $this->to.' 23:59:59'])
            ->whereHas('timetable', function($q) {
                $q->userHasAccessToData($this->user);
            })
            ->paid();
        if($this->categoryId) {

        }
        if($this->timetableIds && count($this->timetableIds)) {
            $q->whereIn('orders.timetable_id', $this->timetableIds);
        }
        if($this->paySystems && count($this->paySystems)) {
            $q->whereIn('orders.pay_system', $this->paySystems);
        }
        return $q;
    }

    public function initialOrderItemsQuery() {
        $q = OrderItem::whereHas('order', function($q) {
            $q->whereBetween('orders.created_at', [$this->from.' 00:00:00', $this->to.' 23:59:59'])
                ->whereHas('timetable', function($q) {
                    $q->userHasAccessToData($this->user);
                })->paid();
            if($this->timetableIds && count($this->timetableIds)) {
                $q->whereIn('orders.timetable_id', $this->timetableIds);
            }
            if($this->paySystems && count($this->paySystems)) {
                $q->whereIn('orders.pay_system', $this->paySystems);
            }
        });
        return $q;
    }

}