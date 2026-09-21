@extends('layouts.site')

@section('title', 'Galerie')

@section('content')
<section class="page-hero">
  <div class="wrap"><h1>Galerie photos &amp; vidéos</h1><p>La vie du PPN en images.</p></div>
</section>

<section class="section">
  <div class="wrap">
    <div class="filters">
      <a class="chip {{ ! $type ? 'on' : '' }}" href="{{ route('site.galerie') }}">Tout</a>
      <a class="chip {{ $type === 'photo' ? 'on' : '' }}" href="{{ route('site.galerie', ['type' => 'photo']) }}"><i class="fas fa-image"></i> Photos</a>
      <a class="chip {{ $type === 'video' ? 'on' : '' }}" href="{{ route('site.galerie', ['type' => 'video']) }}"><i class="fas fa-video"></i> Vidéos</a>
    </div>

    @if ($medias->isEmpty())
      <p class="empty">Aucun contenu pour le moment.</p>
    @else
      <div class="media-grid">@foreach ($medias as $m) @include('site._media', ['m' => $m]) @endforeach</div>
      @if ($medias->hasPages())
        <div class="pager">
          @if ($medias->onFirstPage()) <span class="dis">‹</span> @else <a href="{{ $medias->previousPageUrl() }}">‹</a> @endif
          <span class="cur">{{ $medias->currentPage() }} / {{ $medias->lastPage() }}</span>
          @if ($medias->hasMorePages()) <a href="{{ $medias->nextPageUrl() }}">›</a> @else <span class="dis">›</span> @endif
        </div>
      @endif
    @endif
  </div>
</section>
@endsection
