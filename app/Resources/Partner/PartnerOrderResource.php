<?php

namespace App\Resources\Partner;

use Illuminate\Http\Resources\Json\JsonResource;

class PartnerOrderResource extends JsonResource {

    public function toArray($request = null) {

        $items = $this->orderItems;
        $itemsArr = [];
        foreach($items as $item) {
            $tempArr = [
                'section_id'    => $item->section_id,
                'pricegroup_id' => $item->pricegroup_id,
                'ticket_id'     => $item->ticket_id,
                'row'           => $item->row,
                'seat'          => $item->seat,
                'seat_id'       => $item->seat_id,
                'price'         => $item->price,
            ];
            if($this->paid) {
                $tempArr['barcode'] = $item->barcode;
            }
            $itemsArr[] = $tempArr;
        }
        return [
            'id'                => $this->id,
            'original_price'    => round($this->original_price, 2),
            'fee'               => round($this->external_fee, 2),
            'discount'          => round($this->discount, 2),
            'final_price'       => round($this->price, 2),
            'paid'              => $this->paid,
            'sent'              => $this->sent,
            'created_at'        => $this->created_at,
            'items'             => $itemsArr
        ];
    }
}
