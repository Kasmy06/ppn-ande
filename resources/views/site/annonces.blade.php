@extends('layouts.site')

@section('title', 'Annonces')
@section('description', "Les annonces du Point de Présence Numérique d'Andé : fermetures, nouveautés, inscriptions.")

@section('content')
<section class="page-hero">
  <div class="wrap"><h1>Annonces</h1><p>Les dernières informations du PPN.</p></div>
</section>

<section class="section">
  <div class="wrap">
    @if ($annonces->isEmpty())
      <p class="empty">Aucune annonce pour le moment.</p>
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
