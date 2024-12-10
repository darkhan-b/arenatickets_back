<?php

namespace App\Models\Helpers;

class CurrencyHelper {

    public static function currencies() {
        return [
            'kzt',
            'rub',
            'uah',
            'usd'
        ];
    }

    public static function currencySymbol() {
        $currency = self::getUserCurrency();
        $symbols = [
            'kzt' => '₸',
            'rub' => '₽',
            'uah' => '₴',
            'usd' => '$',
        ];
        return isset($symbols[$currency]) ? $symbols[$currency] : '';
    }

    public static function getUserCurrency() {
        $currency = session('currency',null);
        if($currency) {
            return $currency;
        }
        $country = session('countryCode',null);
        $currency = 'usd';
        if($country) {
            switch($country) {
                case "KZ":
                    $currency = 'kzt';
                    break;
                case "UA":
                    $currency = 'uah';
                    break;
                case "RU":
                    $currency = 'rub';
                    break;
                default:
                    $currency = 'usd';
                    break;
            }
        }
        session(['currency' => $currency]);
        return $currency;
    }

    public static function getUSDKZTRate() {
        return 460;
    }

}
