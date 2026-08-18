@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="ff-pager">

        {{-- Mobile: prev / next only --}}
        <div class="ff-pager-mobile">
            @if ($paginator->onFirstPage())
                <span class="ff-pager-btn is-disabled">@lang('pagination.previous')</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                    class="ff-pager-btn">@lang('pagination.previous')</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="ff-pager-btn">@lang('pagination.next')</a>
            @else
                <span class="ff-pager-btn is-disabled">@lang('pagination.next')</span>
            @endif
        </div>

        {{-- Desktop: summary + numbered pages --}}
        <div class="ff-pager-desktop">
            <p class="ff-pager-summary">
                Showing <span class="ff-pager-strong">{{ $paginator->firstItem() ?? 0 }}</span>
                to <span class="ff-pager-strong">{{ $paginator->lastItem() ?? 0 }}</span>
                of <span class="ff-pager-strong">{{ $paginator->total() }}</span> results
            </p>

            <div class="ff-pager-pages">
                @if ($paginator->onFirstPage())
                    <span class="ff-pager-btn is-icon is-disabled" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="ff-pager-btn is-icon"
                        aria-label="@lang('pagination.previous')">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="15 18 9 12 15 6" />
                        </svg>
                    </a>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="ff-pager-btn is-disabled">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="ff-pager-btn is-active" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="ff-pager-btn">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="ff-pager-btn is-icon"
                        aria-label="@lang('pagination.next')">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </a>
                @else
                    <span class="ff-pager-btn is-icon is-disabled" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6" />
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
