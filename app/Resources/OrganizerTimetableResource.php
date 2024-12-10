<?php

namespace App\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrganizerTimetableResource extends JsonResource {

    public function toArray($request = null) {

        return array_merge(parent::toArray($request), [
            'datePlaceString'   => $this->datePlaceString,
            'ticketsTotal'      => $this->totalTickets,
            'ticketsSold'       => $this->soldTickets
        ]);
    }
}
