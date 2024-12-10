<div class="submenu">
        <div class="container">
                @foreach($submenu as $link)
                        <a class="@if('/'.Request::path() == localePath($link['url'])) active @endif" href="{{ localePath($link['url']) }}">{{ $link['title'] }}</a>
                @endforeach
        </div>
</div>
