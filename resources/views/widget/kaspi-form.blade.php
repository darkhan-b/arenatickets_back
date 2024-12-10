@extends('layouts.app', ['min' => true])

@section('content')
    <div class="d-flex justify-content-center align-items-center" style="height: 100vh">
        <form action="https://kaspi.kz/online" method="post" id="kaspikz-form">
            <input type="hidden" name="TranId" value="{{ $order->hash }}">
            <input type="hidden" name="OrderId" value="{{ $order->id }}">
            <input type="hidden" name="Amount" value="{{ $order->price * 100 }}">
            <input type="hidden" name="Service" value="Topbilet">
{{--            <input type="hidden" name="returnUrl" value="https://api.topbilet.kz/order/kaspi-success/{{ $order->id }}">--}}
            <input type="hidden" name="returnUrl" value="https://topbilet.kz">
{{--            <input type="hidden" name="returnUrl" value="https://api.topbilet.kz/kaspi/test">--}}
            <input type="hidden" name="Signature" value="">
            <button class="btn btn-success px-4" type="submit">Оплатить</button>
        </form>
    </div>
@endsection

@section('footer')

@endsection
