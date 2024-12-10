{{--<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU" type="text/javascript"></script>--}}
{{--<script src="/js/vendor/jquery.min.js"></script>--}}
{{--<script src="/js/vendor/parallax.min.js" defer></script>--}}
{{--<script src="/js/vendor/jquery.fancybox.min.js" defer></script>--}}
{{--<script src="/js/vendor/owl.carousel.min.js" defer></script>--}}
{{--<script src="/js/vendor/nouislider.min.js" defer></script>--}}
{{--<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>--}}
{{--<script src="/js/vendor/vue.min.js" defer></script>--}}
{{--<script src="/js/vendor/multi-animated-counter.js" defer></script>--}}
{{--<script src="/js/vendor/wow.min.js" defer></script>--}}
{{--<script src="{{ mix('js/script.js') }}" defer></script>--}}
@if(isset($widget) && $widget)
{{--    <script src="https://cdn.jsdelivr.net/npm/vue@2.7.8"></script>--}}
{{--    <script src="https://cdn.jsdelivr.net/npm/vue@2.7.8/dist/vue.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.js"></script>--}}
{{--    <script src="https://cdnjs.cloudflare.com/ajax/libs/vuex/4.0.2/vuex.cjs.min.js"></script>--}}
{{--    <script src="https://unpkg.com/vue-router/dist/vue-router.js"></script>--}}
{{--    <script src="https://unpkg.com/vue-router@4.1.3/dist/vue-router.global.js"></script>--}}
    <script src="{{ mix('js/widget.js') }}" defer></script>
@else
{{--    <script src="{{ mix('js/site.js') }}" defer></script>--}}
@endif
