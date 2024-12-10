<div class="event-teaser position-relative" @if(isset($fadeEffect) && $fadeEffect) data-aos="zoom-out" @endif>
    <a href="{{ $event->link }}">
        <img src="{{ $event->teaserBig }}" alt="{{ $event->title }}"/>
    </a>
    @if($event->genre)
        <div class="genre">{{ $event->genre->title }}</div>
    @endif
    <div class="info position-absolute">
        <div class="title">
            <a href="{{ $event->link }}">
                {{ $event->title }}
            </a>
        </div>
        <div class="subtitle">{{ $event->dates }}</div>
    </div>
</div>
