@extends('layouts.app', ['min' => true])

<?php
    $widgetLink = '<script type="text/javascript" src="https://api.topbilet.kz/js/topbilet_iframe.js"></script>';
    $buttonTag = '<button data-event-id="{eventId}" data-timetable-id="{timetableId}" data-source="{source}" data-tkn="{userToken}" class="tb-widget">Купить билет</button>';

?>

@section('content')

    <div class="container mt-5 doc-narrow pt-4">
        <h2 class="mb-4">Виджет Topbilet.kz</h2>
        <p>Для работы виджета по продаже билетов на мероприятие требуется следующее:</p>

        <ul>
            <li>
                1.	На страницу, где будет использоваться виджет подгрузить следующий скрипт (подгружать один раз, даже если кнопок по продаже на странице будет несколько):<br/>
                <div class="code-block"><code>{!! htmlspecialchars($widgetLink) !!}</code></div>
            </li>
            <li>
                2.	В место, где должна быть кнопка, запускающая виджет, вставить следующий html-код:<br/>
                <div class="code-block">
                    <code>{!! htmlspecialchars($buttonTag) !!}</code>
                </div>
                <ul class="mt-3">
                    <li>вместо <code>{eventid}</code> нужно вставить id события,</li>
                    <li>вместо <code>{timetableId}</code> поставить id сеанса,</li>
                    <li>вместо <code>{source}</code> поставить название сайта / агента, который осуществляет продажу (по этому параметру topbilet сможет отслеживать кол-во продаж через платформу партнера)</li>
                    <li>вместо <code>{userToken}</code> поставить токен пользователя, если авторизован (не обязательный параметр, можно не указывать)</li>
                    <li>класс <code>tb-widget</code> обязателен, без него виджет работать не будет</li>
                </ul>
            </li>
        </ul>
    </div>

@endsection

@section('footer')
    <style>
        li {
            margin-bottom: 20px;
        }
        .doc-narrow {
            max-width: 800px;
        }
        .code-block {
            margin-top: 15px;
            border: 1px solid #ddd;
            background: #f8f8f8;
            padding: 10px 20px;
            border-radius: 8px;
        }
    </style>
@endsection
