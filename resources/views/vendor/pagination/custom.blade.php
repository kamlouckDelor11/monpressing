@if ($paginator->hasPages())
    <div class="custom-pagination-container" role="navigation" aria-label="Pagination des objectifs">
        <ul class="pagination-list">
            {{-- Lien Précédent --}}
            @if ($paginator->onFirstPage())
                <li class="pagination-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="pagination-link" aria-hidden="true">&laquo;</span>
                </li>
            @else
                <li class="pagination-item">
                    <a class="pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&laquo;</a>
                </li>
            @endif

            {{-- Éléments de la Pagination --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="pagination-item disabled" aria-disabled="true"><span class="pagination-link">{{ $element }}</span></li>
                @endif

                {{-- Tableau de Liens --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="pagination-item active" aria-current="page"><span class="pagination-link">{{ $page }}</span></li>
                        @else
                            <li class="pagination-item"><a class="pagination-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Lien Suivant --}}
            @if ($paginator->hasMorePages())
                <li class="pagination-item">
                    <a class="pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&raquo;</a>
                </li>
            @else
                <li class="pagination-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="pagination-link" aria-hidden="true">&raquo;</span>
                </li>
            @endif
        </ul>
    </div>
@endif