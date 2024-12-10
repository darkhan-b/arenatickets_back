@php
    $properties = [
        ['phone', 'phone.svg'], 
        ['email', 'mail.svg'], 
        ['address', 'pin.svg']
    ];
    $socialNetworks = ['facebook', 'instagram', 'vkontakte'];
    $leftMenu = [
        ['title' => 'afisha', 'url' => 'afisha'],    
        ['title' => 'repertoire', 'url' => 'repertoire'],    
        // ['title' => 'buy_tickets', 'url' => 'tickets'],    
        // ['title' => 'history_of_theatre', 'url' => 'about/history'],    
        ['title' => 'contacts', 'url' => 'about/contacts'],    
    ];
    $bottomMenu = [
        ['title' => 'private_policy', 'url' => 'confidentiality'],
        ['title' => 'public_offer', 'url' => 'offer'],
    ];
@endphp

<div id="topmenu-shadow"></div>
<div id="topmenu">
    <div class="container container-mob-g0">
        <div class="topmenu__content">
            <div class="container-mob-20">
                <div class="row">
                    <div class="col-5">
                        <div class="left-part pt-md-5 pt-3">
                            <section class="langs">
                                @foreach(config('app.alt_langs') as $lang)
                                    <a class="text-uppercase language me-2 @if($lang === app()->getLocale()) active @endif" href="/{{ $lang }}/{{ substr(Request::path(), 3) }}">{{ $lang }}</a>
                                @endforeach
                            </section>
                        </div>
                    </div>
                    <div class="col-7">
                        <div class="right-part">
                            <div class="position-relative">
                                <section class="properties d-md-block d-none pt-5">
                                    <div class="row">
                                        @foreach($properties as $property)
                                            <div class="col-md-4">
                                                <div class="footer-setting">
                                                    <img src="/images/{{ $property[1] }}" alt="" class="pe-2"/>
                                                    @switch($property[0])
                                                        @case('email')
                                                        <a href="mailto:{{ settingProperty($settings, $property[0]) }}">
                                                            {{ settingProperty($settings, $property[0]) }}
                                                        </a>
                                                        @break
                                                        @case('phone')
                                                        <a href="tel:{{ settingProperty($settings, 'phone1') }}">
                                                            {{ settingProperty($settings, 'phone1') }}
                                                        </a>
                                                        @break
                                                        @default
                                                        {{ settingProperty($settings, $property[0]) }}
                                                    @endswitch
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                                <a class="position-absolute pointer" id="close-top-menu">
                                    <img src="/images/close_w.svg" alt=""/>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="topmenu__center">
                <div class="">
                    <div class="row h-100">
                        <div class="col-lg-5">
                            <div class="left-part content container-mob-20">
                                <section class="menu big-menu">
                                    @foreach($leftMenu as $lm)
                                        <a href="{{ localePath($lm['url']) }}">{{ __($lm['title']) }}</a>
                                    @endforeach
                                </section>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="right-part">
                                <section class="menu middle-menu">
                                    <div class="row content">
                                        <div class="col-lg-4">
                                            <a class="h-link d-block d-md-none menu-collapser" data-bs-toggle="collapse"
                                               href="#collapse1"
                                               role="button"
                                               aria-expanded="true"
                                               aria-controls="collapse1">
                                                {{ __('about_theatre') }}
                                                <img src="/images/down2.svg" alt=""/>
                                            </a>

                                            <div class="collapse show collapse-only-mob" id="collapse1">
                                                <a class="h-link d-md-block d-none" href="{{ localePath('about') }}">{{ __('about_theatre') }}</a>
                                                <a href="{{ localePath('about/history') }}">{{ __('history') }}</a>
                                                <a href="{{ localePath('about/museum') }}">{{ __('museum') }}</a>
                                                <a href="{{ localePath('about/3dtour') }}">{{ __('3dtour') }}</a>
                                                <a href="{{ localePath('about/hall') }}">{{ __('hall_scheme') }}</a>
                                                <a href="{{ localePath('about/etiquete') }}">{{ __('theatre_etiquete') }}</a>
                                                <a href="{{ localePath('about/partners') }}">{{ __('partners') }}</a>
                                                <a href="{{ localePath('news') }}">{{ __('news') }}</a>
                                                <a href="{{ localePath('about/contacts') }}">{{ __('contacts') }}</a>
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <a class="h-link d-block d-md-none menu-collapser" data-bs-toggle="collapse"
                                               href="#collapse2"
                                               role="button"
                                               aria-expanded="false"
                                               aria-controls="collapse2">
                                                {{ __('theatre_people') }}
                                                <img src="/images/down2.svg" alt=""/>
                                            </a>
                                            <div class="collapse collapse-only-mob" id="collapse2">
                                                <a class="h-link d-md-block d-none" href="{{ localePath('people') }}">{{ __('theatre_people') }}</a>
                                                <a href="{{ localePath('people/actor') }}">{{ __('actors') }}</a>
                                                <a href="{{ localePath('people/admin') }}">{{ __('admin_personnel') }}</a>
                                                <a href="{{ localePath('people/manager') }}">{{ __('management') }}</a>
{{--                                                <a href="{{ localePath('people/tech') }}">{{ __('technical_personnel') }}</a>--}}
                                            </div>
                                        </div>
                                        <div class="col-lg-4">
                                            <a class="h-link d-block d-md-none menu-collapser" data-bs-toggle="collapse"
                                               href="#collapse3"
                                               role="button"
                                               aria-expanded="false"
                                               aria-controls="collapse3">
                                                {{ __('information') }}
                                                <img src="/images/down2.svg" alt=""/>
                                            </a>
                                            <div class="collapse collapse-only-mob" id="collapse3">
                                                <a class="h-link d-md-block d-none" href="{{ localePath('tickets') }}">{{ __('information') }}</a>
                                                <a href="{{ localePath('cashdesk') }}">{{ __('cashdesk') }}</a>
                                                <a href="{{ localePath('tour') }}">{{ __('tour_center') }}</a>
                                                <a href="{{ localePath('contests') }}">{{ __('competition') }}</a>
                                                <a href="{{ localePath('government_purchases') }}">{{ __('government_purchases') }}</a>
                                                <a href="{{ localePath('adaldyk_alany') }}">{{ __('adaldyk_alany') }}</a>
                                                <a href="{{ localePath('for_press') }}">{{ __('for_press') }}</a>
                                                <a href="{{ localePath('media') }}">{{ __('media_on_us') }}</a>
                                                <a href="{{ localePath('vacancies') }}">{{ __('vacancies') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-block d-md-none bottom-mobile-menu">
                @foreach($properties as $property)
                    <div class="container">
                        <div class="footer-setting">
                            <div class="d-inline-block img-wrp">
                                <img src="/images/{{ $property[1] }}" alt=""/>
                            </div>
                            <div class="d-inline-block">
                                @switch($property[0])
                                    @case('email')
                                    <a href="mailto:{{ settingProperty($settings, $property[0]) }}">
                                        {{ settingProperty($settings, $property[0]) }}
                                    </a>
                                    @break
                                    @case('phone')
                                    <a href="tel:{{ settingProperty($settings, 'phone1') }}">
                                        {{ settingProperty($settings, 'phone1') }}
                                    </a>
                                    @break
                                    @default
                                    {{ settingProperty($settings, $property[0]) }}
                                @endswitch
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {{--            <div class="topmenu__bottom">--}}
            {{--                <div class="row">--}}
            {{--                    <div class="col-lg-5">--}}
            {{--                        <div class="left-part">--}}
            {{--                            <section class="social">--}}
            {{--                                <div class="row">--}}
            {{--                                    @foreach($socialNetworks as $sn)--}}
            {{--                                        <div class="col-md-4 footer-social">--}}
            {{--                                            <a href="{{ settingProperty($settings, $sn) }}" target="_blank" rel="nofollow" class="text-capitalize">--}}
            {{--                                                {{ $sn }}--}}
            {{--                                            </a>--}}
            {{--                                        </div>--}}
            {{--                                    @endforeach--}}
            {{--                                </div>--}}
            {{--                            </section>--}}
            {{--                        </div>--}}
            {{--                    </div>--}}
            {{--                    <div class="col-lg-7">--}}
            {{--                        <div class="right-part">--}}
            {{--                            @foreach($bottomMenu as $lm)--}}
            {{--                                <a class="bottom-link" href="{{ localePath($lm['url']) }}">{{ __($lm['title']) }}</a>--}}
            {{--                            @endforeach--}}
            {{--                        </div>--}}
            {{--                    </div>--}}
            {{--                </div>--}}
            {{--            </div>--}}
        </div>
    </div>
</div>
