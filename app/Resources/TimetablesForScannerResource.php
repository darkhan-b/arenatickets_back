<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TimetablesForScannerResource extends JsonResource {

    public function toArray($request = null) {

        return array_merge(parent::toArray($request), [
            'soldTickets'  => $this->soldTickets,
        ]);
    }
}
