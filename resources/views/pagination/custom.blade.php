@if ($paginator->hasPages())
  <div class="pagination">
    @if ($paginator->onFirstPage())
      <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
    @else
      <a class="page-btn" href="{{ $paginator->previousPageUrl() }}"><i class="fas fa-chevron-left"></i></a>
    @endif

    @foreach ($elements as $element)
      @if (is_string($element))
        <span class="page-btn disabled">{{ $element }}</span>
      @endif

      @if (is_array($element))
        @foreach ($element as $page => $url)
          @if ($page == $paginator->currentPage())
            <span class="page-btn active">{{ $page }}</span>
          @else
            <a class="page-btn" href="{{ $url }}">{{ $page }}</a>
          @endif
        @endforeach
      @endif
    @endforeach

    @if ($paginator->hasMorePages())
      <a class="page-btn" href="{{ $paginator->nextPageUrl() }}"><i class="fas fa-chevron-right"></i></a>
    @else
      <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
    @endif
  </div>
@endif
