<?php

namespace App\Models\Reports;

use App\Models\API\TelegramAPI;
use App\Models\Specific\Order;

class DailyStatReport {
    
    public static function generateReportForDate($from, $to) {
        $from = $from.' 00:00:00';
        $to = $to.' 23:59:59';
        $paidSum = Order::whereBetween('created_at', [$from, $to])
            ->paid()
            ->sum('price');
        $paidCount = Order::whereBetween('created_at', [$from, $to])
            ->paid()
            ->count();
        $refunds = Order::withTrashed()
            ->whereBetween('refunded_at', [$from, $to])
            ->sum('price');
        $paidFee = Order::whereBetween('created_at', [$from, $to])->paid()->sum('external_fee') + Order::whereBetween('created_at', [$from, $to])->paid()->sum('internal_fee');
        $refundedFee = Order::withTrashed()->whereBetween('refunded_at', [$from, $to])->sum('external_fee') + Order::withTrashed()->whereBetween('refunded_at', [$from, $to])->sum('internal_fee');
        $fee = $paidFee - $refundedFee;
        $netto = $paidSum - $refunds;
        return compact('paidSum', 'paidCount', 'refunds', 'netto', 'fee');
    }
    
    public static function stringifyReport($data, $additionalTitle = '') {
        $arr = ['paidSum', 'paidCount', 'refunds', 'netto', 'fee'];
        $str = $additionalTitle;
        foreach($arr as $a) {
            if($str) $str .= "\n\n";
            $str .= $a.': '.number_format($data[$a]);
        }
        return $str;
    }
    
    public static function sendDailyReport($from = null, $to = null, $title = '') {
        if(!$from) $from = date('Y-m-d');
        if(!$to) $to = date('Y-m-d');
        $message = self::stringifyReport(self::generateReportForDate($from, $to), $title);
        TelegramAPI::sendMessage($message);
    }

}