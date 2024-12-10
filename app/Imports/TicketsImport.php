<?php

namespace App\Imports;
use App\Models\Specific\Seat;
use App\Models\Specific\Ticket;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TicketsImport implements ToModel, WithHeadingRow {

    public $timetableId;
    public $sectionId;
    public $pricegroup;

    public function  __construct($timetableId, $sectionId = null, $pricegroup = null) {
        $this->timetableId = $timetableId;
        $this->sectionId = $sectionId;
        $this->pricegroup = $pricegroup;
    }

    public function model(array $row) {
        $barcode = $row['Штрихкод'] ?? null;
        if(!$barcode) return null;
        $price = $row['Цена'] ?? null;
        if($this->pricegroup) $price = $this->pricegroup->price;
        if(!$price) return null;
        $sectionId = $row['Сектор'] ?? $this->sectionId ?? null;
        $seatRow = $row['Ряд'] ?? null;
        if($seatRow) $seatRow = (string)$seatRow;
        $seat = $row['Место'] ?? null;
        $seatId = null;
        if($sectionId && $seatRow && $seat) {
            $seatId = Seat::where([
                'section_id' => $sectionId,
                'row'        => $seatRow,
                'seat'       => $seat
            ])->value('id');
        }
        if(($seatRow || $seat) && !$seatId) return null;
        if(Ticket::where(['timetable_id' => $this->timetableId, 'barcode' => $barcode])->exists()) {
            return null;
        }
        return new Ticket([
            'timetable_id' => $this->timetableId,
            'section_id'   => $sectionId,
            'pricegroup_id'=> $this->pricegroup?->id ?? null,
            'seat_id'      => $seatId,
            'row'          => $seatRow,
            'seat'         => $seat,
            'price'        => $price,
            'barcode'      => $barcode,
        ]);
    }

}
