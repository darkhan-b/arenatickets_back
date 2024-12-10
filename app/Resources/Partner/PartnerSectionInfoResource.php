<?php

namespace App\Resources\Partner;

use App\Models\Types\TimetableType;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerSectionInfoResource extends JsonResource {

    public function toArray($request = null) {

        $data = [
            'amount' => $this['cnt'],
        ];
        if(isset($this['section_id']) && isset($this['section']) && $this['section']) {
            $data['type'] = TimetableType::SECTIONS;
            $data['section'] = $this['section'];
        }
        if(isset($this['pricegroup_id']) && isset($this['pricegroup']) && $this['pricegroup']) {
            $data['type'] = TimetableType::PRICEGROUPS;
            $data['pricegroup'] = [
                'id'    => $this['pricegroup']['id'],
                'title' => $this['pricegroup']['title'],
                'price' => $this['pricegroup']['price'],
            ];
        }
        return $data;
    }
}
