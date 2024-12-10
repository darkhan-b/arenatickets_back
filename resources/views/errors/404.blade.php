<?php app()->setLocale('ru'); ?>

@extends('layouts.app', ['min' => true])

@section('content')

    <section class="page-wrap">
        <section class="error">
            <div class="content text-center mt-5">
                <a href="/" class="error__logo"></a>
                <img src="/images/logo.png" alt="{{ __('error') }}"
                     style="max-width: 400px;"
                     class="error__img"
                     title="404">
            </div><!--content-->
        </section><!--error-->
    </section><!--page-wrap-->

@endsection
