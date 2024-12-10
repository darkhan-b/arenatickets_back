<?php


namespace App\Models\Reports;

class Timeline {

    public static function prepareCollectionForChart($collection, $from = null, $to = null) {
        if(!$to) { $to = date('Y-m-d'); }
        if(!$from) { $from = date('Y-m-d',strtotime('-30 days')); }
        $now = date('Y-m-d');
        $col = $collection->keyBy('date')->toArray();
        $labels = [];
        $data = [];
        while($from <= $to) {
            $labels[] = date('d.m',strtotime($from));
            $data[] = isset($col[$from]) ? $col[$from]['value'] : ($now <= $from ? null : 0);
            $from = date('Y-m-d',strtotime($from) + (60*60*24));
        }
        $res = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Продажи',
                    'data'  => $data,
                    'backgroundColor' => '#f87979',
                ]
            ]
        ];
        return $res;
    }
}