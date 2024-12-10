<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FreedomPayAPI {

    private string $merchantId;
    private string $secretKey;
	public string $url = 'https://api.freedompay.kz';
//    public string $url = 'https://api.paybox.money';

    public function __construct($legal_entity_id = null) {
        $this->merchantId = env('FREEDOM_PAY_MERCHANT_ID');
        $this->secretKey = env('FREEDOM_PAY_SECRET_KEY_RECEIVE');
    }

    public function initiateTransaction($order) : array {
        $result = [];
        $timetable = $order->timetable;
        $show = $timetable?->show;
        $eventTitle = $show ? $show->title : '-';
        $ticketsNum = $order->orderitems()->count();

        $params = [
            'pg_order_id'           => (string)$order->id,
            'pg_merchant_id'        => $this->merchantId,
            'pg_amount'             => (int)$order->price,
            'pg_description'        => $ticketsNum.' Билет(а/ов) на '.$eventTitle.', Аренатикетс',
            'pg_salt'               => (string)rand(21,43433),
            'pg_request_method'     => 'POST',
            'pg_lifetime'           => 5*60,
            'pg_currency'           => 'KZT',
            'pg_can_reject'         => 1,
            'pg_encoding'           => 'UTF-8',
            'pg_check_url'          => env('APP_URL').'/freedom/callback/check/'.$order->client_id,
            'pg_result_url'         => env('APP_URL').'/freedom/callback/result/'.$order->client_id,
            'pg_success_url'        => env('APP_WIDGET_URL').'/'.$order->client_id.'/order/'.$order->id.'/'.$order->hash.'?res=success&tid='.$timetable->id.'&thash='.$timetable->uuid,
            'pg_failure_url'        => env('APP_WIDGET_URL').'/'.$order->client_id.'/order/'.$order->id.'/'.$order->hash.'?res=failure&tid='.$timetable->id.'&thash='.$timetable->uuid,
            'pg_success_url_method' => 'GET',
            'pg_failure_url_method' => 'GET',
//            'pg_payment_system'     => 'EPAYWEBKZT',
            'pg_user_phone'         => preg_replace('/[^0-9]+/', '', $order->phone),
            'pg_user_email'         => $order->email,
            'pg_user_contact_email' => $order->email,
            'pg_param1'             => $eventTitle,
            'pg_param2'             => $ticketsNum,
//            'pg_testing_mode'       => 1,
            'pg_language'           => 'ru'
        ];

        $scriptName = 'init_payment.php';

        $params['pg_sig'] = $this->makeSign($scriptName, $params);

        if ($array = $this->freedomRequest($scriptName, $params)) {
            if (isset($array['pg_redirect_url'])) {
                $result['data'] = $array['pg_redirect_url'];
                $result['status'] = 200;
            } else {
                $msg = "Ошибка при обработке данных. ";
                $msg .= "Номер ошибки: {$array['pg_error_code']}.";
                $msg .= "Описание ошибки: {$array['pg_error_description']}.";
                $result['data'] = $msg;
                $result['status'] = 500;
            }
        } else {
            $result['data'] = "Ошибка инициализации платежа";
            $result['status'] = 500;
        }
        return $result;
    }

    public function cancelPayment($order) {
        $result = ['status' => 404, 'data' => ''];

        $params = [
            'pg_merchant_id'    => $this->merchantId,
            'pg_payment_id'     => $order->pay_id,
            'pg_refund_amount'  => 0,
            'pg_salt'           => rand(21,43433),
        ];

        $scriptName = 'revoke.php';
        $params['pg_sig'] = $this->makeSign($scriptName, $params, true);

        if ($array = $this->freedomRequest($scriptName,$params)) {
            if (isset($array['pg_status']) && $array['pg_status'] == 'ok') {
                $result['status'] = 200;
            } else {
                $result['status'] = 500;
                $result['data'] = $array;
            }
        }
        return $result;
    }

    public function makeFlatParamsArray($arrParams, $parent_name = '') : array {
        $arrFlatParams = [];
        $i = 0;
        foreach ($arrParams as $key => $val) {
            $i++;
            /**
             * Имя делаем вида tag001subtag001
             * Чтобы можно было потом нормально отсортировать и вложенные узлы не запутались при сортировке
             */
            $name = $parent_name . $key . sprintf('%03d', $i);
            if (is_array($val)) {
                $arrFlatParams = array_merge($arrFlatParams, $this->makeFlatParamsArray($val, $name));
                continue;
            }
            $arrFlatParams += array($name => (string)$val);
        }
        return $arrFlatParams;
    }

    public function makeSign( $scriptName, $arrParams) : string {
        unset($arrParams['pg_sig']);
        $requestForSignature = $this->makeFlatParamsArray($arrParams); // Превращаем объект запроса в плоский массив
        ksort($requestForSignature); // Сортировка по ключю
        if($scriptName) array_unshift($requestForSignature, $scriptName); // Добавление в начало имени скрипта
        $requestForSignature[] = $this->secretKey; // Добавление в конец секретного ключа
        return md5(implode(';', $requestForSignature)); // Полученная подпись
    }

    public function freedomRequest($scriptName, $params) {
        $url = $this->url.'/'.$scriptName;
        Log::error('freedom send data');
        Log::error($url);
        Log::error($params);
        $response = Http::post($url, $params);
        $responseBody = $response->body();
        Log::error($responseBody);
        try {
            $xml = simplexml_load_string($responseBody);
            $json = json_encode($xml);
            return json_decode($json, TRUE);
        } catch (\Exception $e) {
            Log::error('freedom error');
            Log::error($e->getMessage());
        }
        return false;
    }


    public function responseXML($script, $status, $description) : string {
        $resultParams = array(
            'pg_salt'           => (string)rand(21,43433),
            'pg_status'         => $status,
            'pg_description'    => $description
        );
        $resultParams['pg_sig'] = $this->makeSign($script, $resultParams);
        return
            "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                <response>
                      <pg_salt>".$resultParams['pg_salt']."</pg_salt>
                      <pg_status>".$resultParams['pg_status']."</pg_status>
                      <pg_description>".$resultParams['pg_description']."</pg_description>
                      <pg_sig>".$resultParams['pg_sig']."</pg_sig>
                </response>";
    }



//    public function checkStatus($order_id, $whole = true) {
//
//        $result = 0;
//        $params = [
//            'pg_merchant_id'    => $this->merchantId,
//            'pg_order_id'       => $order_id,
//            'pg_salt'           => rand(21,43433)
//        ];
//
//        $scriptName = 'get_status.php';
//        $params['pg_sig'] = $this->makeSign($scriptName, $params,true);
//
//        if($array = $this->freedomRequest($scriptName,$params)) {
//            if (isset($array['pg_transaction_status']) && $array['pg_transaction_status'] == 'ok') {
//                $result = $array['pg_payment_id'];
//                if($whole) {
//                    $result = $array;
//                }
//            } else {
//                $result = 0;
//                if($whole) {
//                    $result = $array;
//                }
//            }
//        }
//        return $result;
//    }





}
