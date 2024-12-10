@extends('layouts.app', ['min' => true])

@section('content')

    <div class="container">
        <div class="d-flex flex-column justify-content-center align-items-center mx-auto vh-100" style="min-width: 280px; max-width: 320px">
            @foreach($orderItems as $oi)
                <a class="btn btn-themed d-block my-3 w-100" 
                   href="/passkit/{{ $order->id }}/{{ $order->hash }}/{{ $oi->id }}">
                    {{ $oi->fullSeatName }}
                </a>
            @endforeach
        </div>
    </div>

@endsection
