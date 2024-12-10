<?php

namespace App\Collections;

use App\Resources\OrganizerTimetableResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrganizerTimetableCollection extends ResourceCollection {

    public function toArray($request) {
        return [
            'data' => OrganizerTimetableResource::collection($this->collection),
            'total' => $this->total(),
            'count' => $this->count(),
            'per_page' => $this->perPage(),
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage(),
            'total_pages' => $this->lastPage()
        ];
    }
}
