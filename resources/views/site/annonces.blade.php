@extends('layouts.site')

@section('title', 'Annonces')
@section('description', "Les annonces du Point de Présence Numérique d'Andé : fermetures, nouveautés, inscriptions.")

@section('content')
<section class="page-hero">
  <div class="wrap"><h1>Annonces</h1><p>Les dernières informations du PPN.</p></div>
</section>

<section class="section">
  <div class="wrap">
    <form class="search-bar" method="GET" action="{{ route('site.annonces') }}">
      <input type="search" name="q" value="{{ $q }}" placeholder="Rechercher une annonce…" aria-label="Rechercher"/>
      <button class="btn btn-green" type="submit"><i class="fas fa-search"></i> Rechercher</button>
      @if ($q !== '')<a class="chip" href="{{ route('site.annonces') }}">Effacer</a>@endif
    </form>

    @if ($annonces->isEmpty())
      <p class="empty">{{ $q !== '' ? 'Aucune annonce ne correspond à votre recherche.' : 'Aucune annonce pour le moment.' }}</p>
    @else
      <div class="annonces-list">@foreach ($annonces as $a) @include('site._annonce', ['a' => $a]) @endforeach</div>

      @if ($annonces->hasPages())
        <div class="pager">
          @if ($annonces->onFirstPage()) <span class="dis">‹</span> @else <a href="{{ $annonces->previousPageUrl() }}">‹</a> @endif
          <span class="cur">{{ $annonces->currentPage() }} / {{ $annonces->lastPage() }}</span>
          @if ($annonces->hasMorePages()) <a href="{{ $annonces->nextPageUrl() }}">›</a> @else <span class="dis">›</span> @endif
        </div>
      @endif
    @endif
  </div>
</section>
@endsection
