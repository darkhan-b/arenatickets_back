<?php

namespace App\Models\API;

use Illuminate\Support\Facades\Http;

class TelegramAPI {

    private static $key = '';
    private static $username = '';
    private static $botname = '';
    private static $chatid = '';

    public static function sendMessage($message = '') {
        $url = "https://api.telegram.org/bot".self::$key."/sendMessage?chat_id=".self::$chatid."&text=".urlencode($message);
        $response = Http::get($url);
        return $response->json();
    }


}
