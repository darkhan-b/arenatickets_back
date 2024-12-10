@php
    $full = !(isset($min) && $min);
    $headerTransparent = isset($headerTransparent) ? $headerTransparent : false;
@endphp

        <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('parts.meta-title')
    @include('parts.metrika')
    @include('parts.assets')
    @yield('head')
</head>
<body id="body" class="body body-@yield('body-class','default')">
@include('parts.body-metrika')
<main>
    <section class="page-wrap">
        @if($full)
            <div class="position-relative">
                @if($headerTransparent)
                    <img class="header-img position-absolute" src="{{ $backgroundImage }}"/>
                @endif
                @include('layouts.header', ['headerTransparent' => $headerTransparent])
                @yield('header-content')
            </div>
        @endif
        @yield('content')
    </section>
</main>
<div class="vue-loader"></div>
@if($full)
    @include('layouts.footer')
@endif
@include('parts.footer_assets', ['full' => $full])
@yield('footer')
</body>
</html>
