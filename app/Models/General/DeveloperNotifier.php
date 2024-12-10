<?php

namespace App\Models\General;

use App\Models\API\TelegramAPI;

class DeveloperNotifier {
    
    public static function error($message) {
        try {
            TelegramAPI::sendMessage($message);    
        } catch (\Exception $e) {};
        
    }

}