@extends('layouts.app')

@section('content')
    <div>
        <div class="widget-part mx-auto">
            <div class="row no-gutters h-100">
                <div class="col">
                    <div class="bg-white">
                        <div class="content-part text-center w-100">
                            <div class="mt-5 pt-5">
                                @if($order->paid)
                                    <div>{{ __('your_order') }} <b>#{{ $order->id }}</b> {{ __('paid') }}</div>
                                    <div>{{ __('tickets_will_be_sent_to_this_email') }} <b>{{ $order->email }}</b></div>
                                    <div class="mt-3">
                                        <a href="{{ $order->ticketsLink }}" class="py-2 btn btn-themed">{{ __('link_to_tickets') }}</a>
                                    </div>
                                @else
                                    <div>{{ __('order_not_paid') }}</div>
                                    @if($order->pay_comment)
                                        <div>{{ $order->pay_comment }}</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection

@section('footer')
    <script>
        (function () {
            if(window && window.parent) {
                window.parent.postMessage(JSON.stringify({
                    action: 'setOrder',
                    data: null
                }), '*');
            }
        });
    </script>
@endsection
