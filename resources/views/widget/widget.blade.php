@extends('layouts.app', ['min' => true])

@section('head')
    <meta name="api-token" content="{{ env('X_API_TOKEN') }}">
    <link rel="stylesheet" href="https://paymentpage.jetpay.kz/shared/merchant.css" />
    <script type="text/javascript" src="https://paymentpage.jetpay.kz/shared/merchant.js"></script>
    <script type="text/javascript" src="/js/vendor/svg-pan-zoom.min.js"></script>
@endsection

@section('body-class', 'widget')

@section('content')
    <div id="vue-event" class="widget-part mx-auto">
        <template>
            <template>
                <top-sidebar/>
            </template>
            <template>
                <scheme-view-switcher class="mobile-switcher"/>
            </template>
            <div class="row g-0">
                <div class="col-md col-12">
                    <div class="right-content position-relative" id="right-content">
                        <div class="content-part">
                            <router-view/>
                        </div>
                        <scheme-view-switcher class="d-md-block d-none"/>
                    </div>
                </div>
                <div class="col-md-auto col-12">
                    <left-sidebar/>
                </div>
            </div>
        </template>
    </div>
@endsection

@section('footer')
    <script>
        (function () {
            var str = window.location.hash;
            var pars = new URLSearchParams(str);
            var addclass = pars.get('additional_class');
            if(addclass) {
                var element = document.getElementById("body");
                element.classList.add(addclass);
            }
            var lang = pars.get('lang');
            if(lang) {
                fetch('/lang?lang='+lang).then((response) => response.json()).then((data) => {
                    window.i18n = data
                    window.lang = lang
                })
            }
            var test = pars.get('test');
            if(test) {
                console.log('test')
            }
            if(window && window.parent) {
                window.parent.postMessage(JSON.stringify({
                    action: 'removeClass',
                    data: 'pt-5'
                }), '*');
            }

            setTimeout(() => { // does not find right content immediately
                let deviceWidth = window.matchMedia("(max-width: 768px)");
                let isIpad = (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 0) || navigator.platform === 'iPad';

                const setHeight = () => {
                    const $el = document.getElementById("right-content")
                    if($el) {
                        if(deviceWidth.matches) {
                            $el.style.height = (window.innerHeight + 200) + "px"
                        } else { // on ipad the top sidebar does not go away
                            $el.style.height = (window.innerHeight - 150) + "px" // no idea why, just a hack
                        }
                    }
                };

                if (deviceWidth.matches || isIpad) {
                    window.addEventListener("resize", setHeight);
                    setHeight();
                }
            }, 100);

        })();
    </script>
@endsection
