@include('parts.topmenu')

<header class="header @if($headerTransparent) transparent @endif d-flex align-items-center" >
    @if($headerTransparent)
        <div class="header-gradient"></div>
    @endif
    <div class="container h-100 header-content">
        <div class="row align-items-center h-100">
            <div class="col-md-auto col-6 text-center order-md-0 order-1">
                <a href="{{ localePath('') }}">
                    <img class="logo-img" 
                         src="{{ $headerTransparent ? '/images/logo_white_'.app()->getLocale().'.svg' : '/images/logo_blue_'.app()->getLocale().'.svg' }}" 
                         alt="лого"/>
                </a>
            </div>
            <div class="col-md-auto col-3 order-md-1 order-0">
                <div class="dropdown lang-dropdown">
                    <a class="text-uppercase pointer"
                       id="langDropdown"
                       no-caret
                       data-bs-toggle="dropdown"
                       aria-expanded="false">
                        {{ app()->getLocale() }}
                        <img src="{{ $headerTransparent ? '/images/down.svg' : '/images/down_color.svg' }}" alt=""/>
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="langDropdown">
                        @foreach(config('app.alt_langs') as $lang)
                            <li>
                                <a class="dropdown-item text-uppercase" href="/{{ $lang }}/{{ substr(Request::path(), 3) }}">{{ $lang }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>

            </div>
            <div class="col-md-auto col-3 text-end order-md-2 order-2">
                <a class="menu-btn" id="menu-toggler">
                    <img src="{{ $headerTransparent ? '/images/menu_white.svg' : '/images/menu.svg' }}" alt="меню"/>
                    <span class="d-none d-md-inline">{{ __('menu') }}</span>
                </a>
            </div>
            <div class="col-md order-md-3 text-end d-none d-md-block">
                @foreach($menu as $m)
                    @if($m->header)
                        <a href="{{ localePath($m->url) }}" class="menu-item @if('/'.Request::path() == localePath($m->url) || \Illuminate\Support\Facades\View::getSection('activeUrl', '') == $m->url) active @endif">
                            {{ $m->title }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

    </div>
</header>
