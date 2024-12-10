@extends('layouts.app', ['min' => true])

@section('content')

    <?php
    $xmlCheckResponse = '
<?xml version="1.0" encoding="UTF-8"?>
<response>
    <txn_id>111111</txn_id>
    <result>0</result>
    <sum>10000.00</sum>
    <fields>
        <field1 name="name">имя пользователя</field1>
        <field2 name="show">название события</field2>
    </fields>
    <comment>успешно</comment>
</response>';

    $xmlPayResponse = '
<?xml version="1.0" encoding="UTF-8"?>
<response>
    <txn_id>111111</txn_id>
    <result>0</result>
    <sum>10000.00</sum>
    <fields>
        <field1 name="name">имя пользователя</field1>
        <field2 name="show">название события</field2>
    </fields>
    <prv_txn>10005432</prv_txn>
    <comment>успешно</comment>
</response>';
    ?>

    <div class="container mt-5 doc-narrow pt-4 mb-5">
        <h2 class="mb-4">Вебхук для оплат через kaspi.kz</h2>
        <div>
            Отправлять GET запросы на <code>{{ env('APP_URL') }}/kaspi/webhook</code> с одобренного ip адреса.
        </div>

        <h5 class="mt-5 mb-4">Проверка заказа</h5>
        <div>Входящие параметры:
            <ul>
                <li>command - должно быть <b>check</b></li>
                <li>account - номер заказа (по системе topbilet)</li>
                <li>txn_id - номер транзакции (по системе kaspi)</li>
            </ul>
        </div>
        <div class="mt-3">Пример запроса: <code>{{ env('APP_URL') }}/kaspi/webhook?command=check&account=10005432&txn_id=111111</code></div>
        <div class="mt-3">
            <div>Пример успешного ответа (коды ошибок ниже):</div>
            @include('docs.xml-block', ['xml' => $xmlCheckResponse])
        </div>

        <h5 class="mt-5 mb-4">Оплата заказа</h5>

        <div>Входящие параметры:
            <ul>
                <li>command - должно быть <b>pay</b></li>
                <li>account - номер заказа (по системе topbilet)</li>
                <li>txn_id - номер транзакции (по системе kaspi)</li>
                <li>sum - сумма оплаты (в тенге, должна совпадать с суммой в ответе при проверке заказа)</li>
                <li>txn_date - дата оплаты в формате yyyymmddhhmmss</li>
            </ul>
        </div>
        <div>Пример запроса: <code>{{ env('APP_URL') }}/kaspi/webhook?command=pay&account=10005432&txn_id=111111&txn_date=20221001123306&sum=10000.00</code></div>
        <div class="mt-3">
            <div>Пример успешного ответа (коды ошибок ниже):</div>
            @include('docs.xml-block', ['xml' => $xmlPayResponse])
        </div>

        <h5 class="mt-5 mb-4">Коды ошибок</h5>
        <div>
            <ul>
                <li>0 - успешно</li>
                <li>1 - заказ не найден</li>
                <li>2 - заказ отменен</li>
                <li>3 - заказ уже оплачен</li>
                <li>4 - платеж в обработке</li>
                <li>5 - другая ошибка</li>
                <li>6 - сумма оплаты отличается от суммы заказа</li>
            </ul>
        </div>
    </div>
@endsection