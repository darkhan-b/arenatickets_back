<?php

namespace App\Resources\Partner;

use Illuminate\Http\Resources\Json\JsonResource;

class PartnerTimetablesResource extends JsonResource {

    public function toArray($request = null) {

        return [
            'id'                => $this->id,
            'date'              => $this->date,
            'title'             => $this->show ? $this->show->getTranslations('title') : [],
            'type'              => $this->type,
            'venue_scheme_id'   => $this->venue_scheme_id,
            'venue'             => [
                'id'                => $this->venue_id,
                'title'             => $this->venue ? $this->venue->getTranslations('title') : []
            ]
        ];
    }
}
