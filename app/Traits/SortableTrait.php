<?php

namespace App\Traits;

trait SortableTrait {

    public function scopeSorted($query) {
        return $query->orderBy('sort_order', 'asc');
    }

}
