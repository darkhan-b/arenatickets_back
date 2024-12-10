@unless ($breadcrumbs->isEmpty())
    <div class="container">
        <div class="breadcrumb" itemscope itemtype="https://schema.org/BreadcrumbList">
            @foreach ($breadcrumbs as $breadcrumb)
                @if (!is_null($breadcrumb->url) && !$loop->last)
                    <div itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a itemprop="item" href="{{ $breadcrumb->url }}" class="pager__link"><span itemprop="name">{{ $breadcrumb->title }}</span></a>
                        <i class="pager__colon"></i>
                    </div>
                @else
                    <div itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                <span class="pager__active" itemprop="item" >
                    <span itemprop="name">
                    {{ $breadcrumb->title }}
                    </span>
                </span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endunless
