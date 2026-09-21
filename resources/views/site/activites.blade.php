@extends('layouts.site')

@section('title', 'Activités')

@section('content')
<section class="page-hero">
  <div class="wrap"><h1>Nos activités</h1><p>Formations, ateliers, événements et accompagnement.</p></div>
</section>

<section class="section">
  <div class="wrap">
    <div class="filters">
      <a class="chip {{ ! $categorie ? 'on' : '' }}" href="{{ route('site.activites', ['periode' => $periode]) }}">Toutes</a>
      @foreach (\App\Models\Activite::CATEGORIES as $cle => $label)
        <a class="chip {{ $categorie === $cle ? 'on' : '' }}" href="{{ route('site.activites', ['categorie' => $cle, 'periode' => $periode]) }}">{{ $label }}</a>
      @endforeach
      <span class="sep"></span>
      <a class="chip {{ $periode === 'a_venir' ? 'on' : '' }}" href="{{ route('site.activites', array_filter(['categorie' => $categorie])) }}">À venir</a>
      <a class="chip {{ $periode === 'passees' ? 'on' : '' }}" href="{{ route('site.activites', array_filter(['categorie' => $categorie, 'periode' => 'passees'])) }}">Passées</a>
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
