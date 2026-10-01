@extends('layouts.site')

@section('title', 'Galerie')

@section('content')
<section class="page-hero">
  <div class="wrap">
    <h1>Galerie photos &amp; vidéos</h1>
    <p>La vie du PPN en images.</p>
    <div class="no-print" style="margin-top:14px">
      <button type="button" class="btn btn-ghost" onclick="window.print()"><i class="fas fa-print"></i> Imprimer</button>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <form class="search-bar no-print" method="GET" action="{{ route('site.galerie') }}">
      @if ($type)<input type="hidden" name="type" value="{{ $type }}">@endif
      <input type="search" name="q" value="{{ $q }}" placeholder="Rechercher une photo, une vidéo…" aria-label="Rechercher"/>
      <button class="btn btn-green" type="submit"><i class="fas fa-search"></i> Rechercher</button>
      @if ($q !== '')<a class="chip" href="{{ route('site.galerie', array_filter(['type' => $type])) }}">Effacer</a>@endif
    </form>

    <div class="filters no-print">
      <a class="chip {{ ! $type ? 'on' : '' }}" href="{{ route('site.galerie', array_filter(['q' => $q])) }}">Tout</a>
      <a class="chip {{ $type === 'photo' ? 'on' : '' }}" href="{{ route('site.galerie', array_filter(['type' => 'photo', 'q' => $q])) }}"><i class="fas fa-image"></i> Photos</a>
      <a class="chip {{ $type === 'video' ? 'on' : '' }}" href="{{ route('site.galerie', array_filter(['type' => 'video', 'q' => $q])) }}"><i class="fas fa-video"></i> Vidéos</a>
    </div>

    @if ($medias->isEmpty())
      <p class="empty">{{ $q !== '' ? 'Aucun contenu ne correspond à votre recherche.' : 'Aucun contenu pour le moment.' }}</p>
    @else
      <div class="media-grid">@foreach ($medias as $m) @include('site._media', ['m' => $m]) @endforeach</div>
      @if ($medias->hasPages())
        <div class="pager no-print">
          @if ($medias->onFirstPage()) <span class="dis">‹</span> @else <a href="{{ $medias->previousPageUrl() }}">‹</a> @endif
          <span class="cur">{{ $medias->currentPage() }} / {{ $medias->lastPage() }}</span>
          @if ($medias->hasMorePages()) <a href="{{ $medias->nextPageUrl() }}">›</a> @else <span class="dis">›</span> @endif
        </div>
      @endif
    @endif
  </div>
</section>
@endsection
