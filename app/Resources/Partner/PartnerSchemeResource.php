<?php

namespace App\Resources\Partner;

use App\Models\Types\TimetableType;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerSchemeResource extends JsonResource {

    public function toArray($request = null) {
        $data = parent::toArray($request);
        unset($data['venue_id']);
        foreach($data['sections'] as $index => $section) {
            unset($section['created_at']);
            unset($section['updated_at']);
            unset($section['venue_scheme_id']);
            unset($section['note']);
            $data['sections'][$index] = $section;
        }
        return $data;
    }
}
