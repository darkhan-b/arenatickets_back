<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('parts.admin_assets')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1" />
    <title>Админ панель | {{ env('APP_NAME') }}</title>
    @yield('head')
</head>
<body class="body body-@yield('body-class','default')">
<div class="wrapper" id="app">
    <sidebar-component></sidebar-component>
    <div class="main-panel">
        <div class="content pb-5">
            @include('layouts.admin.messages')
            <div>
                <router-link to=""></router-link>
            </div>
            <router-view></router-view>
        </div>
        <div class="dropdown-hover-shadow"></div>
        <div class="vue-loader"></div>
        @include('layouts.admin.footer')
    </div>
</div>
</body>
</html>
