@if ($paginator->hasPages())
    <div class="paginator text-center">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <a rel="prev" class="pages__prev" aria-label="@lang('pagination.previous')">
                <img src="/images/left.svg" alt=""/>
            </a>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="pages__prev" aria-label="@lang('pagination.previous')">
                <img src="/images/left.svg" alt=""/>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <a href="#" class="pages__link">...</a>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pages__link active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="pages__link">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="pages__next" aria-label="@lang('pagination.next')">
                <img src="/images/right.svg" alt=""/>
            </a>
        @else
            <a rel="next" class="pages__next" aria-label="@lang('pagination.next')">
                <img src="/images/right.svg" alt=""/>
            </a>
        @endif
    </div>
@endif
