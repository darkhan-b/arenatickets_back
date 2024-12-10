@extends('pdfs.pdflayout')

@section('title','Билеты заказа №'.$order->id)

@section('content')

    <?php
    $showPrice = !$order->hide_price;
    $isInvitation = $order->pay_system === 'invitation';
	$isReservation = $order->reservation;
    ?>

    @foreach($tickets as $ticket)
        @if($ticket->barcode)
            <div class="ticket-wrapper">
                <div>&nbsp;</div>
                {{--                <div class="ticket-background" @if($design->getMedia()->count() > 0) style="background: url({{ str_replace(env('APP_URL'),'',$design->image()) }}) no-repeat center; background-size: contain;" @endif></div>--}}
                <div class="ticket-background" @if($design->getMedia()->count() > 0) style="background: url({{ str_replace(env('APP_URL'),'',$design->media()->first()->getPath()) }}) no-repeat center; background-size: contain;" @endif></div>
                <div class="content">
{{--                    <div class="separator" style="top: 200px;"></div>--}}
                    @if($design->conditions_exist)
                        <div class="dashed" style="top: 560px;"></div>
                    @endif
                    @if($design->ticketn_exists)
                        <h2 class="position-absolute" style="@if($design->ticketn_r) right: {{ $design->ticketn_r }}px; @else left: {{ $design->ticketn_l }}px; @endif top: {{ $design->ticketn_t }}px;">
                            {{ $isReservation ? __('booking') : __('electronic_ticket') }} # {{ str_pad($ticket->id, 8, '0', STR_PAD_LEFT) }}</h2>
                    @endif

                    {{--                    <img class="position-absolute" style="left: 119px; top: 120px; width: 110px;" src="{{ public_path('/images/logo.png') }}"/>--}}

                    @if($event->imagePrimarySrc('teaser'))
                        <img class="position-absolute" style="left: 119px; top: 300px; width: 360px; object-fit: cover; object-position: center; overflow: hidden" src="{{ public_path($event->imagePrimarySrc('main', true)) }}"/>
                    @endif

                    @if($design->title_exists)
                        <h1 class="position-absolute" style="@if($design->title_r) right: {{ $design->title_r }}px; @else left: {{ $design->title_l }}px; @endif top: {{ $design->title_t }}px;
                        width: 800px;
                        text-overflow: ellipsis;
                        overflow:hidden;
                        white-space: nowrap;">
                            {{ $event->title }}
                        </h1>
                    @endif

                    @if($design->order_exists)
                        <div class="position-absolute right-img-content" style="@if($design->order_r) right: {{ $design->order_r }}px; @else left: {{ $design->order_l }}px; @endif top: {{ $design->order_t }}px;"><b>{{ __('order_number') }}: </b>{{ $ticket->order_id }}</div>
                    @endif
                    @if($design->user_exists && !$isInvitation)
                        <div class="position-absolute right-img-content" style="@if($design->user_r) right: {{ $design->user_r }}px; @else left: {{ $design->user_l }}px; @endif top: {{ $design->user_t }}px;"><b>{{ __('name') }}: </b>{{ $order->name }}</div>
                    @endif
                    @if($design->date_exists)
                        <div class="position-absolute right-img-content" style="@if($design->date_r) right: {{ $design->date_r }}px; @else left: {{ $design->date_l }}px; @endif top: {{ $design->date_t }}px;"><b>{{ __('date') }}: </b>{{ date('d.m.Y, H:i', strtotime($timetable->date)) }}</div>
                    @endif
                    @if($design->venue_exists)
                        <div class="position-absolute right-img-content" style="@if($design->venue_r) right: {{ $design->venue_r }}px; @else left: {{ $design->venue_l }}px; @endif top: {{ $design->venue_t }}px;"><b>{{ __('venue') }}: </b>{{ \Illuminate\Support\Str::limit($venue->title, 30, '...') }}</div>
                    @endif
                    @if($design->section_exists)
                        <div class="position-absolute right-img-content" style="@if($design->section_r) right: {{ $design->section_r }}px; @else left: {{ $design->section_l }}px; @endif top: {{ $design->section_t }}px;">
                            @if($ticket->section_id)
                                <b>{{ __('sector') }}: </b>{{ $ticket->section ? $ticket->section->title : 'Входной' }}
                            @elseif($ticket->pricegroup)
                                <b>{{ __('category') }}: </b>{{ $ticket->pricegroup->title }}
                            @endif
                        </div>
                    @endif
                    @if($design->row_exists && $ticket->row)
                        <div class="position-absolute right-img-content" style="@if($design->row_r) right: {{ $design->row_r }}px; @else left: {{ $design->row_l }}px; @endif top: {{ $design->row_t }}px;"><b>{{ __('row') }}: </b>{{ $ticket->row }}</div>
                    @endif
                    @if($design->seat_exists && $ticket->seat)
                        <div class="position-absolute right-img-content" style="@if($design->seat_r) right: {{ $design->seat_r }}px; @else left: {{ $design->seat_l }}px; @endif top: {{ $design->seat_t }}px;"><b>{{ __('seat') }}:</b> {{ $ticket->seat }}</div>
                    @endif
                    {{--                    @if($design->price_exists && $ticket->price)--}}
                    {{--                        <div class="position-absolute" style="@if($design->price_r) right: {{ $design->price_r }}px; @else left: {{ $design->price_l }}px; @endif top: {{ $design->price_t }}px;"><b>Цена:</b> {{ $ticket->price }}</div>--}}
                    {{--                    @endif--}}
                    @if($design->price_exists)
                        <div class="position-absolute right-img-content" style="@if($design->price_r) right: {{ $design->price_r }}px; @else left: {{ $design->price_l }}px; @endif top: {{ $design->price_t }}px;">
                            @if($isInvitation)
                                <b>{{ __('invitation') }}</b>@if($showPrice),@endif
                            @endif
                            @if($showPrice)
                                <span>
                                    @if(!$isInvitation)
                                        <b>{{ __('price') }}:</b>
                                    @endif
                                    {{ number_format($ticket->price,0,"."," ") }} тг
                                </span>
                            @endif
                            @if($ticket->specialDiscount)
                                ({{ $ticket->specialDiscount->name }})
                            @elseif($ticket->pricegroup)
                                ({{ $ticket->pricegroup->title }})
                            @endif
                        </div>
                    @endif

                    @if($design->conditions_exist)
                        <div class="dashed" style="top: 1108px;"></div>
                    @endif

                    @if($design->barcode_exists && !$isReservation)
                        <div class="position-absolute" style="@if($design->barcode_r) right: {{ $design->barcode_r }}px; @else left: {{ $design->barcode_l }}px; @endif top: {{ $design->barcode_t }}px;">
                            {!!  DNS1D::getBarcodeHTML($ticket->barcode,"C128",2,60,"black")  !!}
                            <div class="text-center" style="margin-top: 4px;">{{ $ticket->barcode }}</div>
                        </div>
                    @endif
                    @if($design->qr_exists && !$isReservation)
                        <div class="position-absolute" style="@if($design->qr_r) right: {{ $design->qr_r }}px; @else left: {{ $design->qr_l }}px; @endif top: {{ $design->qr_t }}px;">
                            {!!  DNS2D::getBarcodeHTML($ticket->barcode,"QRCODE", 8, 8, "black")  !!}
                        </div>
                        @if($design->bottom_data_duplicate_exists && !$isReservation)
                            <div class="position-absolute" style="left: 119px; top: 1335px;">
                                {!!  DNS2D::getBarcodeHTML($ticket->barcode,"QRCODE", 6, 6, "black")  !!}
                            </div>
                        @endif
                    @endif

{{--                    <div class="separator" style="top: 1500px;"></div>--}}

                    @if($design->conditions_exist)
                        <div class="rules position-absolute" style="left: 119px; top: 560px; width: 950px; font-size: 13px;">
                            <div class="w-50" style="padding-right: 50px;">
                                <p><b>Билетті пайдалану ережелері</b></p>
                                <p>1. Бұл электрондық билет іс-шараға кіру құқығын береді.</p>
                                <p>2. Осы электрондық билетті көшіруге және көшірмелерін үшінші тұлғаларға табыстауға тыйым салынады. Бұл электрондық билеттегі бірегей сәйкестендіргіш (штрих-код) Сіз үшін іс-шараға бір рет кіру құқығын қамтамасыз етеді. Үшінші тұлғалар осыған ұқсас сәйкестендіргіші бар электрондық билетті ұсынған жағдайда Сіз іс-шараға кіру құқығынан айырыласыз.</p>
                                <p>3. Электрондық билеттерді тексеру және іс-шараға кіру – ұйымдастырушымен немесе алаңша әкімшілігімен белгіленген ережелерге сәйкес жүзеге асырылады. Электрондық билетті сәйкестендіру рәсімінен өту үшін және осы электрондық билетті пайдаланудың заңдылығын тексеру мақсатында, жеке басыңызды куәланыдратын құжатты өзіңізбен бірге алып жүруге кеңес береміз.</p>
                                <p>4. Билетті сатып ала отырып Сіз сайтында толық нұсқасы жарияланған Ережелермен келісесіз.</p>
                                <p>5. Іс-шараға белгіленген уақыттан 15 минутқа артық кешіккен көрермендер тіпті билеті бар болған жағдайдың өзінде жіберілмейді.</p>
                                <p>6. Мас күйде келген және өзімен бірге спирттік сусындар алып келген тұлғалар тіпті билеті бар болған жағдайдың өзінде іс-шараға жіберілмейді.</p>
                                <p>7. Билет кассаларынан өзге жерлерден, уәкілетті өкілдерден және таратушылардан сатып алынған билеттер олардың түпнұсқа билеттерімен сәйкес келмеген жағдайда жасанды деп танылуы мүмкін. Мұндай жағдайда осындай билеттің иесі оны пайдалануға әрекеттенетін болса, сондай-ақ осындай билеттерді тарататын болса қылмыстық, әкімшілік жауапкершілікке тартылу тәуекеліне ұшырайды.</p>
                            </div>
                            <div class="w-50">
                                <p><b>Правила использования билета</b></p>
                                <p>1. Настоящий электронный билет дает право на посещение мероприятия.</p>
                                <p>2. Настоящий электронный билет запрещается копировать и передавать копии третьим лицам. Уникальный идентификатор (штрих-код), содержащийся на настоящем электронном билете, гарантирует вам право на однократное посещение мероприятия. Предъявление третьими лицами электронного билета с идентичным идентификатором лишает вас права на посещение мероприятия.</p>
                                <p>3. Проверка электронных билетов и проход на мероприятие осуществляется в соответствии с правилами, установленными организатором или администрацией площадки. Для прохождения процедуры идентификации электронного билета и в целях проверки правомерности использования настоящего электронного билета рекомендовано иметь при себе документ, удостоверяющий личность.</p>
                                <p>4. Приобретая билет Вы соглашаетесь с Правилами, полная версия которых опубликована на сайте.</p>
                                <p>5. Зрители, опоздавшие на мероприятие по установленному времени более 15-ти минут, не допускаются, даже при наличии билета.</p>
                                <p>6. Лицам в нетрезвом состоянии, и со своими спиртными напитками, не допускаются на мероприятие, даже при наличии билета.</p>
                                <p>7. Билеты, приобретенные в иных местах, нежели в билетных кассах, у уполномоченных представителей и распространителей могут быть признаны поддельными при несоответствии с их оригинальными билетами. В этом случае владелец такого билета несет риск уголовной, административной ответственности, при попытке его использования, наряду с распространителями этих билетов.</p>
                            </div>
                        </div>
                    @endif

                    @if($design->bottom_data_duplicate_exists)
                        <div class="position-absolute" style="left: 290px; top: 1290px; line-height: 1.4">
                            <h2 >{{ $event->title }}</h2>
                            <div>{{ __('date') }}: <b>{{ date('d.m.Y, H:i', strtotime($timetable->date)) }}</b></div>
                            {{--                        <div>{{ __('hall') }}: <b>{{ \Illuminate\Support\Str::limit($venue->title, 65, '...') }} ({{ $timetable->scheme->title }})</b></div>--}}
                            <div>{{ __('hall') }}: <b>{{ \Illuminate\Support\Str::limit($venue->title, 65, '...') }}</b></div>
                            <div>{{ __('seat') }}: <b>{{ $ticket->fullSeatName }}</b></div>
                        </div>
                    @endif

                    {{--                    <img class="position-absolute" style="right: 119px; top: 1180px;" src="{{ public_path('images/logo2.png') }}"/>--}}
                    {{--                    <img class="position-absolute" style="left: 860px; top: 1565px; width: 220px;" src="{{ public_path('images/logo.png') }}"/>--}}

{{--                    <div class="separator" style="top: 1500px;"></div>--}}
                    {{--                    @if(isset($settings['ticket_address']))--}}
                    {{--                        <div class="position-absolute footer" style="top: 1550px; left: 119px;">--}}
                    {{--                            <div class="font-weight-bold mb-1">Адрес</div>--}}
                    {{--                            <div style="white-space: pre-wrap">{!! $settings['ticket_address']['body'] !!}</div>--}}
                    {{--                        </div>--}}
                    {{--                    @endif--}}

                    @if(isset($settings['ticket_contacts']))
                        <div class="position-absolute footer" style="top: 1550px; left: 118px; width: 700px;">
                            <div class="font-weight-bold mb-1"><b>{{ __('contacts') }}</b></div>
                            <div style="white-space: pre-wrap">{!! $settings['ticket_contacts']['body'] !!}</div>
                        </div>
                    @endif


                </div>
            </div>
        @endif
    @endforeach


    <style>
        /*h1 {*/
        /*margin-top: -3px;*/
        /*}*/
        .w-50 {
            width: 50%;
            display: inline-block;
            vertical-align: top;
        }
        .w-50 p {
            white-space: pre-wrap;
            margin-bottom: 0;
        }
        .content {
            position: absolute;
            left: 0;
            top: 0;
            right: 0;
        }
        .ticket-background {
            position: absolute;
            left: 0;
            top: 0;
            width: 1240px;
            height: 1754px;
            background-size: 1240px 1754px;
        }
        .separator {
            height: 2px;
            background: #777;
            position: absolute;
            left: 119px;
            right: 119px;
        }
        .h-100 {
            height: 100%;
        }
        .w-100 {
            width: 100%;
        }
        .ticket-wrapper {
            white-space: nowrap;
            page-break-after: always;
            page-break-inside: avoid;
            /*background: green;*/
            margin: 0;
            position: relative;
            left: 0;
            top: 0;
            /*right: 10px;*/
            /*margin-right: 10px;*/
            /*width: 1120px;*/
            /*height: 1440px;*/
            /*right: 0;*/
            /*bottom: 0;*/
        }
        .ticket-wrapper:last-of-type {
            page-break-after: initial;
            /*overflow-wrap: normal !important;*/
            /*white-space: normal !important;*/
        }
        .position-absolute {
            position: absolute;
        }
        body, html {
            font-size: 20px;
            margin: 0 !important;
        }
        .footer {
            font-size: 19px;
            line-height: 1.4;
        }
        .dashed {
            border-top: 3px dashed #ccc;
            height: 2px;
            position: absolute;
            left: 119px;
            right: 119px;
        }
        .right-img-content {
            max-width: 400px;
            white-space: nowrap;
        }

    </style>
@endsection

