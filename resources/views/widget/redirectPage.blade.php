@extends('layouts.app', ['min' => true])

@section('body-class', 'widget')

@section('content')
    <div class="container" id="pay-result-page">
        @if($result === 'success')
            <div class="row">
                <div class="col-md col-12">
                    <div class="text-center">
                        <img src="/images/payment_success.svg" alt="success"/>
                        <h1 class="mt-3">Заказ успешно оплачен!</h1>
                        <p class="text-muted">
                            Вам на почту отправлено сообщение с вашими билетами. Вы также можете просмотреть их в личном кабинете
                        </p>
                    </div>
                </div>
                <div class="col-12 col-xl-4 offset-xl-2 col-lg-5 offset-lg-1 col-md-6 offset-md-0 mt-md-0 mt-3">
                    @if($show)
                        <div class="card">
                            <div class="card-header fw-bold text-center">Детали заказа</div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tbody>
                                    <tr>
                                        <td class="text-muted">Мероприятие:</td>
                                        <td>{{ $show->title }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Дата и время:</td>
                                        <td>{{ $show->dateString }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Места:</td>
                                        <td>
                                            @foreach($order->orderItems as $oi)
                                                <div>{{ $oi->fullSeatName }}</div>
                                            @endforeach
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        @endif
        @if($result === 'fail')
            <div class="row">
                <div class="col">
                    <div class="text-center">
                        <img src="/images/payment_fail.svg" alt="fail"/>
                        <h1 class="mt-3">Не удалось оформить заказ</h1>
                        {{--                            <p class="text-muted">--}}
                        {{--                                Вам на почту отправлено сообщение с вашими билетами. Вы также можете просмотреть их в личном кабинете--}}
                        {{--                            </p>--}}
                    </div>
                </div>
            </div>

        @endif
    </div>
@endsection

@section('footer')
    <script>
        (function () {
            if(window && window.parent) {
                window.parent.postMessage(JSON.stringify({
                    action: 'removeClass',
                    data: 'pt-5'
                }), '*');
                window.parent.postMessage(JSON.stringify({
                    action: 'setOrder',
                    data: null
                }), '*');
            }
        })();
    </script>
@endsection

<style>
    #pay-result-page {
        margin-top: 90px;
    }
    #pay-result-page h1 {
        font-weight: 700;
        text-transform: none;
        font-size: 34px;
    }
    #pay-result-page .card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        max-width: 370px;
    }
    #pay-result-page .card-header {
        font-size: 16px;
        background: #DBDFE1;
        border-bottom: none;
    }
    #pay-result-page .card-body {
        font-size: 14px;
    }
</style>
