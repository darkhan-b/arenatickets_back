@if($timetable->show)
    <div class="timetable-teaser" data-aos="zoom-out">
        <div class="row">
            <div class="col-md-2">
                <div class="row align-items-center g-0">
                    <div class="col-auto">
                        <div class="day">{{ date('d', strtotime($timetable->date)) }}</div>
                    </div>
                    <div class="col">
                        <div class="ps-xl-3 ps-lg-2">
                            <div class="month">{{ __(date('F', strtotime($timetable->date)).'_') }} {{ date('Y', strtotime($timetable->date)) }}</div>
                            <div class="day-of-week text-muted">{{ __(date('l', strtotime($timetable->date))) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mt-md-0 mt-2 mb-3 mb-md-0">
                <div class="image">
                    <a href="{{ $timetable->link }}">
                        <img src="{{ $timetable->show->teaser }}" alt="{{ $timetable->show->title }}"/>
                    </a>
                </div>
            </div>
            <div class="col-md-7">
                <div class="row mb-3">
                    <div class="col">
                        <div class="dots">
                            @if($timetable->show->genre)
                                <div class="bubble me-2">{{ $timetable->show->genre->title }}</div>
                            @endif
                            @if($timetable->show->age)
                                <div class="bubble">{{ $timetable->show->age }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-auto d-none d-md-block">
                        <div class="duration text-muted">{{ presentDuration($timetable->show->duration) }}</div>
                    </div>
                </div>
                <div class="row align-items-center mb-md-5 mb-2">
                    <div class="col">
                        <div class="title">{{ $timetable->show->title }}</div>
                    </div>
                    <div class="col-auto d-none d-md-block">
                        <div class="start">{{ date('H:i', strtotime($timetable->date)) }}</div>
                    </div>
                </div>
                <div class="row align-items-center">
                    <div class="col-md">
                        <?php $dot = false; ?>
                        @if($timetable->show->hall)
                            <span class="me-3">{{ $timetable->show->hall->title }}</span>
                            <?php $dot = true; ?>
                        @endif
                        @if($timetable->show->language)
                            @if($dot) <span class="dot me-3"></span> @endif
                            <span class="me-3">{{ __('on_'.$timetable->show->language) }}</span>
                            <?php $dot = true; ?>
                        @endif
                        @if($timetable->show->price)
                            @if($dot) <span class="dot me-3"></span> @endif
                            <span class="me-3">{{ $timetable->show->price }}</span>
                        @endif
                    </div>
                    <div class="d-md-none d-block col mt-md-0 mt-3">
                        <div class="duration text-muted">{{ presentDuration($timetable->show->duration) }}</div>
                        <div class="start">{{ date('H:i', strtotime($timetable->date)) }}</div>
                    </div>
                    <div class="col-auto mt-md-0 mt-3">
                        <div class="buy">
{{--                            <a class="btn-themed d-inline-block" href="{{ $timetable->show->ticket_url }}" target="_blank" rel="nofollow">--}}
                            <a class="btn-themed d-inline-block" href="{{ $timetable->purchase_link }}" target="_blank" rel="nofollow">
                                {{ __('buy_ticket') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
