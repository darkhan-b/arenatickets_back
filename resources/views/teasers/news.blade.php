<div class="news-teaser" data-aos="fade-in">
    <div class="date">{{ dateFormat($item->date) }}</div>
    <div class="category">{{ $item->category }}</div>
    @if($item->image)
        <a href="{{ $item->link }}">
            <img src="{{ $item->image }}" alt=""/>
        </a>
    @endif
    <a class="title d-block @if($item->image) has-image @endif" href="{{ $item->link }}">
        {{ $item->title }}
    </a>
    <div class="teaser">{!! $item->teaser !!}</div>
</div>
