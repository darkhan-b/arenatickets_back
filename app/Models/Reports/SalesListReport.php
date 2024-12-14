<?php

namespace App\Models\Reports;

use App\Exports\ReportExport;
use App\Models\Specific\OrderItem;
use App\Models\Specific\Ticket;
use App\Models\Specific\Timetable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SalesListReport
{

    public $date_from;
    public $date_to;
    public $timetable_ids;
    public $show_ids;
    public $venue_ids;
    public $organizer_ids;
    public $client_ids;
    public $category_ids;
    public $data;
    public $report_type;
    public $is_organizer;

    public function __construct($params)
    {
        $this->date_from = isset($params['date_from']) ? $params['date_from'] : null;
        $this->date_to = isset($params['date_to']) ? $params['date_to'] : null;
        $this->timetable_ids = isset($params['timetable_ids']) ? (is_array($params['timetable_ids']) ? $params['timetable_ids'] : [$params['timetable_ids']]) : [];
        $this->show_ids = isset($params['show_ids']) ? (is_array($params['show_ids']) ? $params['show_ids'] : [$params['show_ids']]) : [];
        $this->venue_ids = isset($params['venue_ids']) ? (is_array($params['venue_ids']) ? $params['venue_ids'] : [$params['venue_ids']]) : [];
        $this->organizer_ids = isset($params['organizer_ids']) ? (is_array($params['organizer_ids']) ? $params['organizer_ids'] : [$params['organizer_ids']]) : [];
        $this->client_ids = isset($params['client_ids']) ? (is_array($params['client_ids']) ? $params['client_ids'] : [$params['client_ids']]) : [];
        $this->category_ids = isset($params['category_ids']) ? (is_array($params['category_ids']) ? $params['category_ids'] : [$params['category_ids']]) : [];
        $this->report_type = isset($params['report_type']) ? $params['report_type'] : 'sales';
        $this->is_organizer = isset($params['is_organizer']) && $params['is_organizer'];
        $this->data = null;
    }

    public function hasData() {
        return $this->data && count($this->data) > 0;
    }

    public function generate() {

        if($this->report_type === 'unsold') {
            $query = Ticket::with(['section','pricegroup','timetable','timetable.show','timetable.venue','timetable.venue.city'])
                ->where('sold', 0);
            if(count($this->timetable_ids) > 0) {
                $query->whereIn('timetable_id',$this->timetable_ids);
            }
            if(count($this->show_ids) > 0) {
                $event_timetable_ids = Timetable::whereIn('show_id',$this->show_ids)->pluck('id')->toArray();
                $query->whereIn('timetable_id', $event_timetable_ids);
            }
            $this->data = $query->get();
            return;
        }

        $query = OrderItem::with('section')
            ->whereHas('orderWithTrashed',function($q) {
                if($this->report_type === 'sales') {
                    $q->whereNull('deleted_at');
                }
                if($this->report_type === 'refunds') {
                    $q->whereNotNull('refunded_at');
                }
                $q->where('paid', 1);
                if($this->date_from) {
                    $q->where($this->report_type === 'refunds' ? 'refunded_at' : 'created_at', '>=', $this->date_from.' 00:00:00');
                }
                if($this->date_to) {
                    $q->where($this->report_type === 'refunds' ? 'refunded_at' : 'created_at', '<=', $this->date_to.' 23:59:59');
                }
                if($this->is_organizer) {
                    $q->where('show_to_organizer', 1);
                }
            })
            ->with('orderWithTrashed.timetable');

        $query->with('orderWithTrashed.timetable.show');
        if(count($this->timetable_ids) > 0) {
            $query->whereIn('timetable_id',$this->timetable_ids);
        }
        if(count($this->venue_ids) > 0) {
            $query->whereHas('timetable', function($q) {
                $q->whereIn('venue_id', $this->venue_ids);
            });
        }
        if(count($this->organizer_ids) > 0) {
            $query->whereHas('timetable', function($q) {
                $q->whereHas('show', function ($q) {
                    $q->whereIn('organizer_id', $this->organizer_ids);
                });
            });
        }
        if(count($this->client_ids) > 0) {
            $query->whereHas('timetable', function($q) {
                $q->whereIn('client_id', $this->client_ids);
            });
        }
        if(count($this->category_ids) > 0) {
            $query->whereHas('timetable', function($q) {
                $q->whereHas('show', function ($q) {
                    $q->whereIn('category_id', $this->category_ids);
                });
            });
        }
        if(count($this->show_ids) > 0) {
            $event_timetable_ids = Timetable::whereIn('show_id',$this->show_ids)->pluck('id')->toArray();
            $query->whereIn('timetable_id', $event_timetable_ids);
        }
        $query->orderBy('id','desc');
        $this->data = $query->get();
    }

    public function dataAdapter() {
        $data = [];
        if ($this->data && count($this->data) > 0) {
            $intro_rows = $this->generateIntroRows();
            foreach($intro_rows as $r) {
                $data[] = $r;
            }
            $totalOriginal = 0;
            $totalDiscount = 0;
            $total = 0;
            $fee = 0;
            $refundable_fee = 0;
            foreach($this->data as $d) {
                if($d->orderWithTrashed || $this->report_type === 'unsold') {
                    $row = $this->generateRow($d);
                    $data[] = array_values($row);
                    $original = $d->original_price >= $d->price ? $d->original_price : $d->price;
                    $totalOriginal += $original;
					$totalDiscount += ($original - $d->price + $d->weightedPromocodeDiscount);
					$total += ($d->price - $d->weightedPromocodeDiscount);
                    if($this->report_type !== 'unsold') {
                        $fee += $d->weightedServiceFee;
                        $refundable_fee += $d->weightedRefundableFee;
                    }
                }
            }
            $data[] = [];
            if($this->report_type === 'unsold') {
                $row = ['Итого', '', '', '', '', '', '', '', '', $total];
            } else {
                $row = ['Итого', '', '', '', '', '', '', '', $totalOriginal, $totalDiscount, $total, $fee, $refundable_fee, ($total + $fee)];
            }
            $data[] = $row;
        }
        return $data;
    }

    public function toExcel() {
        ini_set("memory_limit", '1000M');
        set_time_limit(200);

        $data = $this->dataAdapter();
        $export = new ReportExport($data);
        $filename = 'report_'.$this->report_type.'_'.date('Y-m-d_H:i:s').'.xlsx';
        $path = '/reports/'.$filename;
        if(Excel::store($export, 'public'.$path)) return env('APP_URL').'/storage'.$path;
        return null;
    }

    public function generateIntroRows() {

        if($this->report_type === 'unsold') {
            return [
                ['Отчет по непроданным билетам'],
                ['Время выгрузки',(string)date('Y-m-d H:i:d')],
                ['Кем выгружено', Auth::id().' - '.Auth::user()->name],
                ['Выбранные id событий',count($this->show_ids) > 0 ? implode(', ',$this->show_ids) : 'Все'],
                ['Выбранные id сеансов',count($this->timetable_ids) > 0 ? implode(', ',$this->timetable_ids) : 'Все'],
                count($this->client_ids) ? ['Выбранные id клиентов',implode(', ',$this->client_ids)] : [''],
                [''],
                [
                    'ID билета',
                    'ID сеанса',
                    'Наименование события',
                    'Дата сеанса',
                    'Город',
                    'Площадка',
                    'Сектор',
                    'Ряд',
                    'Место',
                    'Цена',
                ]
            ];
        }
        return [
            [$this->report_type === 'refunds' ? 'Отчет по возвратам билетов' : 'Отчет по продажам билетов'],
            ['Время выгрузки',(string)date('Y-m-d H:i:d')],
            ['Кем выгружено', Auth::id().' - '.Auth::user()->name],
            ['Заданные даты',$this->date_from.' - '.$this->date_to],
            ['Выбранные id событий',count($this->show_ids) > 0 ? implode(', ',$this->show_ids) : 'Все'],
            ['Выбранные id сеансов',count($this->timetable_ids) > 0 ? implode(', ',$this->timetable_ids) : 'Все'],
            ['Выбранные id площадок',count($this->venue_ids) > 0 ? implode(', ',$this->venue_ids) : 'Все'],
            ['Выбранные id организаторов',count($this->organizer_ids) > 0 ? implode(', ',$this->organizer_ids) : 'Все'],
            ['Выбранные id категорий',count($this->category_ids) > 0 ? implode(', ',$this->category_ids) : 'Все'],
            count($this->client_ids) ? ['Выбранные id клиентов',implode(', ',$this->client_ids)] : [''],
            [''],
            [
                'ID билета',
                'ID заказа',
                'ID сеанса',
                'Наименование события',
                'Дата сеанса',
                'Сектор',
                'Ряд',
                'Место',
                'Начальная цена',
                'Скидка',
                'Цена',
                'Сервисный сбор',
                'Комиссия возвратного билета',
                'Цена после сбора',
                'Штрихкод',
                'Email',
                'Контактные данные',
                'Телефон',
                'Позиция',
                'Компания',
                'Страна',
                'Тип участия',
                'Дата заказа',
                'Тип продажи',
                'Платформа',
                'Комментарий',
                $this->report_type === 'refunds' ? 'Дата возврата' : ''
            ]
        ];
    }

    public function generateRow($d) {
        if ($this->report_type === 'unsold') {
            $row = [
                'ID билета'             => $d->id,
                'ID сеанса'             => $d->timetable_id,
                'Наименование события'  => $d->timetable->show->title ?? '-',
                'Дата сеанса'           => $d->timetable->date ?? '-',
                'Город'                 => $d->timetable->venue->city->title ?? '-',
                'Площадка'              => $d->timetable->venue->title ?? '-',
                'Сектор'                => $d->sectionOrPricegroupTitle,
                'Ряд'                   => $d->row ?: '-',
                'Место'                 => $d->seat ?: '-',
                'Цена'                  => $d->price,
            ];
        } else {
            $startPrice = $d->original_price >= $d->price ? $d->original_price : $d->price;
            $promocodeDiscount = $d->weightedPromocodeDiscount;
    
            $row = [
                'ID билета' => $d->id,
                'ID заказа' => $d->order_id,
                'ID сеанса' => $d->orderWithTrashed->timetable_id ?? '-',
                'Наименование события' => $d->orderWithTrashed->timetable->show->title ?? '-',
                'Дата сеанса' => $d->orderWithTrashed->timetable->date ?? '-',
                'Сектор' => $d->section ? $d->section->title : 'Входной',
                'Ряд' => $d->row ?: '-',
                'Место' => $d->seat ?: '-',
                'Начальная цена' => $startPrice,
                'Скидка' => $startPrice - $d->price + $promocodeDiscount,
                'Цена' => $d->price - $promocodeDiscount,
                'Сервисный сбор' => $d->weightedServiceFee,
                'Комиссия возвратного билета' => $d->weightedRefundableFee,
                'Цена после сбора' => $d->weightedPrice,
                'Штрихкод' => $d->barcode . ' ', // Добавляем пробел, чтобы Excel не форматировал как научное число
                'Email' => $d->orderWithTrashed->email,
                'Контактные данные' => $d->orderWithTrashed->name,
                'Телефон' => $d->orderWithTrashed->phone,
                'Позиция' => $d->orderWithTrashed->position ?? '-',
                'Компания' => $d->orderWithTrashed->company ?? '-',
                'Страна' => $d->orderWithTrashed->country ?? '-',
                'Тип участия' => $this->mapParticipation($d->orderWithTrashed->participation),
                'Дата заказа' => (string)$d->orderWithTrashed->created_at,
                'Тип продажи' => $d->orderWithTrashed->pay_system ?? '-',
                'Платформа' => $d->orderWithTrashed->platform ?? '-',
                'Комментарий' => $d->orderWithTrashed->comment ?? '-',
            ];
    
            if ($d->orderWithTrashed->pay_system === 'forum') {
                $row['Позиция'] = $d->orderWithTrashed->position ?? '-';
                $row['Компания'] = $d->orderWithTrashed->company ?? '-';
                $row['Страна'] = $d->orderWithTrashed->country ?? '-';
                $row['Тип участия'] = $this->mapParticipation($d->orderWithTrashed->participation);
            }
        }
        return $row;
    }

    private function mapParticipation($participation)
    {
        $participationMap = [
            'speaker' => 'Спикер',
            'visitor' => 'Посетитель',
            'exhibitor' => 'Участник выставки',
        ];

        return $participationMap[$participation] ?? $participation;
    }
    
}
