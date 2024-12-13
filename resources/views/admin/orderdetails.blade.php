@php
    $pay_methods = [
        'card' => 'онлайн',
        'cash' => 'касса',
        'invitation' => 'пригласительный',
        'forum' => "бесплатный"
        'organizer' => 'организатор',
        'partner' => 'партнер',
    ];
    $timetable = isset($order) ? $order->timetable : null;
    $event = $timetable ? $timetable->show : null;
    $initiatedBy = $order->initiatedBy;
@endphp

<div class="row mt-3">
    <div class="col-6">
        <div class="">
            <h5>О заказе</h5>
            <table class="table table-striped" style="table-layout: fixed">
                <tbody>
                <tr>
                    <td>Id заказа</td>
                    <td>{{ $order->id }}</td>
                </tr>
                <tr>
                    <td>Событие</td>
                    <td>@if($event)[{{ $event->id }}]@endif {{ $event ? $event->title : '-' }}</td>
                </tr>
                <tr>
                    <td>Сеанс</td>
                    <td>@if($timetable)[{{ $timetable->id }}]@endif {{ $timetable ? dateFormat($timetable->date, true) : '-' }}</td>
                </tr>
                <tr>
                    <td>Начальная сумма</td>
                    <td>{{ presentNum($order->original_price) }}</td>
                </tr>
                <tr>
                    <td>Внутренняя комиссия</td>
                    <td>{{ presentNum($order->internal_fee) }}</td>
                </tr>
                <tr>
                    <td>Верхняя комиссия</td>
                    <td>{{ presentNum($order->external_fee) }}</td>
                </tr>
                @if($order->discount_rate)
                    <tr>
                        <td>Скидка на мероприятие</td>
                        <td>{{ $order->discount_rate }}%</td>
                    </tr>
                @endif
                @if($order->promocode_discount_rate)
                    <tr>
                        <td>Скидка по промокоду</td>
                        <td>{{ $order->promocode_discount_rate }}% ({{ $order->promocode }})</td>
                    </tr>
                @endif
                <tr>
                    <td>Финальная сумма</td>
                    <td>{{ presentNum($order->price) }}</td>
                </tr>
                <tr>
                    <td>Метод оплаты</td>
                    <td>
                        <span class="badge badge-{{ $order->pay_system }}">
                            {{ $order->pay_system && isset($pay_methods[$order->pay_system]) ? $pay_methods[$order->pay_system] : 'не выбран' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Сумма оплаты</td>
                    <td>
                        {{ presentNum($order->pay_sum) }}
                    </td>
                </tr>
                {{--                <tr>--}}
                {{--                    <td>Платформа</td>--}}
                {{--                    <td>{{ $order->platform }}</td>--}}
                {{--                </tr>--}}
                <tr>
                    <td>Создан</td>
                    <td>{{ dateFormat($order->created_at,true,true) }}</td>
                </tr>
                <tr>
                    <td>Статус</td>
                    <td>
                        @if($order->paid)
                            <span class="badge bg-success">Оплачен</span>
                        @else
                            <span class="badge badge-waiting_payment">Создан</span>
                        @endif
                        @if($order->trashed())
                            <span class="badge badge-cancelled">Удален</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Билеты отправлены</td>
                    <td>
                        @if($order->sent) <span class="badge bg-success">Отправлены</span> @else <span class="badge badge-cancelled">Нет</span> @endif
                    </td>
                </tr>
                @if($order->paid)
                    <tr>
                        <td>Id платежа</td>
                        <td style="word-break: break-all">{{ $order->pay_id }}</td>
                    </tr>
                @endif
                {{--                @if($order->paid && $order->pay_system == 'card')--}}
                {{--                    <tr>--}}
                {{--                        <td>Внутренняя ссылка</td>--}}
                {{--                        <td>{{ $order->int_ref }}</td>--}}
                {{--                    </tr>--}}
                {{--                @endif--}}
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-6">
        <h5>О клиенте</h5>
        <div>
            @if($order->email || $order->name || $order->phone)
                <div>
                    @if($initiatedBy == 'organizer')
                        <div><span class="badge badge-waiting_payment">Организатор</span></div>
                    @elseif($initiatedBy == 'kassir')
                        <div><span class="badge bg-success">Кассир</span></div>
                    @else
                        <div><span class="badge badge-delivered">Пользователь</span></div>
                    @endif
                    <div>{{ $order->email }}</div>
                    <div>{{ $order->name }}</div>
                    <div>{{ $order->phone }}</div>
                </div>
            @else
                <div>Клиент еще не ввел данные</div>
            @endif
        </div>
        <div class="mt-4">
            <h5>Билеты</h5>
            <table class="table table-striped">
                <tbody>
                @foreach($order->orderItems as $item)
                    <tr>
                        @if($item->section)
                            <td>Сектор: {{ $item->section->title }}</td>
                        @endif
                        @if($item->row)
                            <td>Ряд: {{ $item->row }}</td>
                        @endif
                        @if($item->seat)
                            <td>Место: {{ $item->seat }}</td>
                        @endif
                        @if(!$item->row && !$item->seat)
                            @if($item->pricegroup)
                                <td colspan="2">{{ $item->pricegroup->title }}</td>
                            @else
                                <td colspan="2">Входной</td>
                            @endif

                        @endif
                        <td>{{ number_format($item->price,0,"."," ") }} тг</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{--        @if($order->paid && in_array($order->pay_system,['card','paybox']))--}}
        {{--            <form method="post" action="/admin/order/{{ $order->id }}/return" class="mt-5 text-right" onsubmit="return confirm('Вы уверены, что хотите вернуть деньги по этому заказу на карту клиенту?')">--}}
        {{--                @csrf--}}
        {{--                <button type="submit" class="btn btn-themed btn-danger text-white">Возврат денег на карту</button>--}}
        {{--            </form>--}}
        {{--        @endif--}}
    </div>
</div>
