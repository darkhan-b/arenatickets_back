@if ($paginator->hasPages())
    <div class="dataTables_wrapper">
        <div class="dataTables_paginate paging_simple_numbers" id="dataTable_paginate">
            <ul class="pagination" role="navigation">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <span class="page-link paginate_button" aria-hidden="true"><i class="ti-angle-left"></i></span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link paginate_button" data-page="{{ ($paginator->currentPage() - 1) }}" rel="prev" aria-label="@lang('pagination.previous')"><i class="ti-angle-left"></i></a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link paginate_button">{{ $element }}</span>
                        </li>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link paginate_button current" data-page="{{ $page }}">{{ $page }}</span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link paginate_button" data-page="{{ $page }}">{{ $page }}</a>
                                </li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link paginate_button" data-page="{{ ($paginator->currentPage() + 1) }}" rel="next" aria-label="@lang('pagination.next')"><i class="ti-angle-right"></i></a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                        <span class="page-link paginate_button" aria-hidden="true"><i class="ti-angle-right"></i></span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
@endif
