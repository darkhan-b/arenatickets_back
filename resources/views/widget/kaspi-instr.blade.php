@extends('layouts.app', ['min' => true])

@section('content')
    <div>
        <div class="widget-part mx-auto">
            <div class="row no-gutters h-100">
                <div class="col">
                    <div class="bg-white">
                        <div class="content-part w-100">
                            <div class="mt-5 pt-5 ps-5">
                                <h1>Оплата заказа через Kaspi</h1>
                                <div style="font-weight: 500; font-size: 22px; border-radius: 8px; border: 1px solid #4BB0FE; padding: 15px 30px;" class="d-inline-block">Заказ № {{ $order->id }}</div>
                                <h5 class="mt-4">Как оплатить через мобильное приложение Kaspi</h5>
                                <ul class="ps-0 mt-4">
                                    <ol class="ps-0 mb-2">1. Авторизуйтесь в приложении Kaspi.kz</ol>
                                    <ol class="ps-0 mb-2">2. Перейдите в раздел “Платежи”, далее введите в строке поиска “Topbilet”</ol>
                                    <ol class="ps-0 mb-2">3. В открывшейся форме укажите номер заказа и нажмите  кнопку “Продолжить”</ol>
                                    <ol class="ps-0">4. Проверьте, что данные верны и перейдите к оплате</ol>
                                    
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    
@endsection
