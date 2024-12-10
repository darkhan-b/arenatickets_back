<?php

namespace App\Models\API;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleAPI
{
    public static function sendUrlToGoogle($url, $action = 'add') {

        $client = new \Google_Client();

        $client->setAuthConfig(base_path(''));
        $client->addScope('https://www.googleapis.com/auth/indexing');

        $httpClient = $client->authorize();
        $endpoint = 'https://indexing.googleapis.com/v3/urlNotifications:publish';

        $url = "https://arenatickets.kz/ru/".$url;

        $content = [
            "url" => $url,
            "type" => $action === 'delete' ? 'URL_DELETED' : 'URL_UPDATED'
        ];

        $response = $httpClient->post($endpoint, [ 'body' => json_encode($content)]);
        $status_code = $response->getStatusCode();

        Log::info('Google response for url '.$url.': '.$status_code);

        return $status_code == 200;
    }

    public static function sendDataToAnalytics($params = []) {
        $url = 'https://www.google-analytics.com/mp/collect?api_secret=xxx&measurement_id=xxx';
        try {
            Http::post($url, $params);
        } catch (\Exception $e) {}
    }

}
