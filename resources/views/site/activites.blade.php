@extends('layouts.site')

@section('title', 'Activités')

@section('content')
<section class="page-hero">
  <div class="wrap"><h1>Nos activités</h1><p>Formations, ateliers, événements et accompagnement — les plus récentes en premier.</p></div>
</section>

<section class="section">
  <div class="wrap">
    <form class="search-bar" method="GET" action="{{ route('site.activites') }}">
      @if ($categorie)<input type="hidden" name="categorie" value="{{ $categorie }}">@endif
      <input type="search" name="q" value="{{ $q }}" placeholder="Rechercher une activité, un lieu…" aria-label="Rechercher"/>
      <button class="btn btn-green" type="submit"><i class="fas fa-search"></i> Rechercher</button>
      @if ($q !== '')<a class="chip" href="{{ route('site.activites', array_filter(['categorie' => $categorie])) }}">Effacer</a>@endif
    </form>

    <div class="filters">
      <a class="chip {{ ! $categorie ? 'on' : '' }}" href="{{ route('site.activites', array_filter(['q' => $q])) }}">Toutes</a>
      @foreach (\App\Models\Activite::CATEGORIES as $cle => $label)
        <a class="chip {{ $categorie === $cle ? 'on' : '' }}" href="{{ route('site.activites', array_filter(['categorie' => $cle, 'q' => $q])) }}">{{ $label }}</a>
      @endforeach
    </div>

    @if ($activites->isEmpty())
      <p class="empty">Aucune activité trouvée pour ces critères.</p>
    @else
      <div class="grid">@foreach ($activites as $a) @include('site._carte', ['a' => $a]) @endforeach</div>

      @if ($activites->hasPages())
        <div class="pager">
          @if ($activites->onFirstPage()) <span class="dis">‹</span> @else <a href="{{ $activites->previousPageUrl() }}">‹</a> @endif
          <span class="cur">{{ $activites->currentPage() }} / {{ $activites->lastPage() }}</span>
          @if ($activites->hasMorePages()) <a href="{{ $activites->nextPageUrl() }}">›</a> @else <span class="dis">›</span> @endif
        </div>
      @endif
    @endif
  </div>
</section>
@endsection
