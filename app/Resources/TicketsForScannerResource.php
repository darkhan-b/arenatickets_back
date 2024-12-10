<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketsForScannerResource extends JsonResource {

    public function toArray($request = null) {

        return array_merge(parent::toArray($request), [
            'fullSeatName'  => $this->fullSeatName,
            'pay_system'    => $this->order ? $this->order->pay_system : ''
        ]);
    }
}
