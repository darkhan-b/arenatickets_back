<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrganizerOrdersResource extends JsonResource {

    public function toArray($request = null) {

        $orderItems = $this->orderItems;
        $orderItems->each->append('fullSeatName');

        return array_merge(parent::toArray($request), [
            'orderItems'  => $orderItems,
        ]);
    }
}
