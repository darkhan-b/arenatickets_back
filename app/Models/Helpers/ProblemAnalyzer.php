<?php

namespace App\Models\Helpers;

use App\Models\Specific\OrderItem;
use Illuminate\Support\Facades\DB;

class ProblemAnalyzer {

    public static function getPaidOrderItemsWithDuplicateBarcodes() {
        $data = OrderItem::whereHas('order', function($q) {
            $q->paid();
        })->select('barcode', DB::raw('COUNT(*) as `count`'))
            ->groupBy('barcode')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        foreach($data as $d) {
            $orderItems = OrderItem::where('barcode', $d->barcode)->whereHas('order', function($q) {
                $q->paid();
            })->get();
            foreach($orderItems as $oi) {
                echo $oi->barcode.' - '.$oi->id.' '.$oi->order_id.'<br>';
            }
            echo '<br/>';
        }
    }

    public static function getPaidTicketsWithoutTicketId() {
        $data = OrderItem::whereHas('order', function($q) {
           $q->paid();
        })->whereNull('ticket_id')->get();
        foreach($data as $d) {
            echo $d->id.' - '.$d->order_id.'<br>';
        }
    }

    public static function getTicketsWithDuplicateBarcodes($timetableId) {
        $data = DB::table('tickets')
            ->where('timetable_id', $timetableId)
            ->select('barcode', DB::raw('COUNT(*) as `count`'))
            ->groupBy('barcode')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        foreach($data as $d) {
            echo $d->barcode.' - '.$d->count.'<br>';
        }
    }

    public static function findOrdersWithTicketsFromOtherTimetables() {
        $data = OrderItem::whereHas('order', function($q) {
            $q->paid();
        })->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('tickets', 'order_items.ticket_id', '=', 'tickets.id')
            ->whereColumn('orders.timetable_id', '!=', 'tickets.timetable_id')
            ->get();
        foreach($data as $d) {
            echo $d->order_id;
            echo '<br/>';
        }
    }

}
