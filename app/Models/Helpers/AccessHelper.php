<?php

namespace App\Models\Helpers;

class AccessHelper {
    
    public static function hasAccessToTestEvents() {
        return auth('api')->id() === 1;
    }

}