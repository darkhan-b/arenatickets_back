<?php

namespace App\Models\API;

use App\Models\Specific\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KaspiAPI {

    private static $url = 'https://kaspi.kz/online';
    private static $id = '';

    public static function qr(Order $order) {
        $response = Http::post(self::$url, [
            'TranId'            => (string)$order->id,
            'OrderId'           => (string)$order->id,
            'Amount'            => ($order->price * 100),
            'Service'           => self::$id,
            'returnUrl'         => 'https://api.arenatickets.kz/order/kaspi-success/'.$order->id,
            'refererHost'       => 'arenatickets.kz',
            'GenerateQrCode'    => true
        ]);
        return $response->json();
    }

    public static function request(Order $order) {
        $response = Http::post(self::$url, [
            'TranId'    => $order->id,
            'OrderId'   => $order->id,
            'Service'   => self::$id,
            'Amount'    => $order->price,
            'returnUrl' => '',
            'Signature' => ''
        ]);
        return $response->json();
    }
}
