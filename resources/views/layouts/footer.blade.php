@php
    $properties = ['address', 'email', 'phone'];
    $socialNetworks = ['facebook', 'instagram', 'vkontakte', 'youtube', 'TikTok', 'Telegram'];
@endphp


<footer class="footer">
    <div class="footer__slogan text-center">
        <div class="container">
            <div class="narrow">
                {{ __('full_title') }}
            </div>
        </div>
    </div>
    <div class="footer__main">
        <div class="container container-mob-g0">
            <div class="row g-0">
                <div class="col-md-3 order-md-0 order-0">
                    <div class="main-left">
                        @foreach($menu as $m)
                            @if($m->footer)
                                <a href="{{ localePath($m->url) }}" class="menu-item @if('/'.Request::path() == localePath($m->url) || \Illuminate\Support\Facades\View::getSection('activeUrl', '') == $m->url) active @endif">
                                    {{ $m->title }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="col-md-6 order-md-1 order-2">
                    @foreach($properties as $property)
                        <div class="bordered-block">
                            <div class="footer-label">{{ __($property) }}</div>
                            <div class="footer-setting">
                                @switch($property)
                                    @case('email')
                                    <a href="mailto:{{ settingProperty($settings, $property) }}">
                                        {{ settingProperty($settings, $property) }}
                                    </a>
                                    @break
                                    @case('phone')
                                    <a href="tel:{{ settingProperty($settings, 'phone1') }}">
                                        {{ settingProperty($settings, 'phone1') }}
                                    </a>
                                    @break
                                    @default
                                    {{ settingProperty($settings, $property) }}
                                @endswitch
                            </div>
                        </div>
                    @endforeach
                    <div class="bordered-block">
                        <div class="footer-setting">
                            <a href="{{ localePath('about/contacts') }}">
                                {{ __('all_contacts') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 order-md-2 order-1 text-end">
                    <div class="main-right">
                        @foreach($menu as $m)
                            @if($m->footer_2)
                                <a href="{{ localePath($m->url) }}" class="menu-item @if('/'.Request::path() == localePath($m->url) || \Illuminate\Support\Facades\View::getSection('activeUrl', '') == $m->url) active @endif">
                                    {{ $m->title }}
                                </a>
                            @endif
                        @endforeach
                        <div class="mt-5 d-none d-md-block">
                            @foreach(config('app.alt_langs') as $lang)
                                <a class="menu-item d-inline-block text-uppercase ps-2 @if($lang === app()->getLocale()) active text-muted @endif"
                                   href="/{{ $lang }}/{{ substr(Request::path(), 3) }}">{{ $lang }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="border-top">
            <div class="container container-mob-g0">
                <div class="row g-0 h-100">
                    @foreach($socialNetworks as $sn)
                        <div class="col-md col-6 footer-social">
                            <a href="{{ settingProperty($settings, $sn) }}" 
                               target="_blank" 
                               rel="nofollow" 
                               class="text-uppercase">
                                {{ $sn }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="footer__copyright">
        <div class="container">
            <div class="row">
                <div class="col-md-6">©{{ date('Y') }}, {{ __('full_title') }}</div>
                <div class="col-md-6 text-md-end mt-md-0 mt-3">
                    {{ __('development') }} - <a target="_blank" rel="nofollow" href="https://offlime.kz">Offlime</a>
                </div>
            </div>
        </div>
    </div>
</footer>
