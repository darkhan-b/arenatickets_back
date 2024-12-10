<?php

namespace App\Traits;

use App\Models\Helpers\AccessHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait ActiveScopeTrait {

    public function scopeActive($query) {
//        if(AccessHelper::hasAccessToTestEvents()) return $query;
        return $query->where('active', 1);
    }

}
