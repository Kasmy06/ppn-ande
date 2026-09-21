@extends('layouts.site')

@section('title', 'Agenda')
@section('description', "Le calendrier des activités du Point de Présence Numérique d'Andé.")

@section('content')
<section class="page-hero">
  <div class="wrap"><h1>Agenda des activités</h1><p>Retrouvez ce qui se passe chaque jour au PPN.</p></div>
</section>

<section class="section">
  <div class="wrap">
    <div class="agenda-nav">
      <a class="chip" href="{{ route('site.agenda', ['mois' => $precedent]) }}"><i class="fas fa-chevron-left"></i></a>
      <h2>{{ ucfirst($mois->translatedFormat('F Y')) }}</h2>
      <a class="chip" href="{{ route('site.agenda', ['mois' => $suivant]) }}"><i class="fas fa-chevron-right"></i></a>
      <a class="chip" href="{{ route('site.agenda') }}">Aujourd'hui</a>
    </div>

    <div class="agenda-scroll">
      <div class="agenda">
        @foreach (['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'] as $nom)<div class="ag-head">{{ $nom }}</div>@endforeach
        @foreach ($jours as $j)
          <div class="ag-day {{ $j['hors_mois'] ? 'off' : '' }} {{ $j['date']->isToday() ? 'today' : '' }}">
            <span class="ag-num">{{ $j['date']->day }}</span>
            @foreach ($j['activites'] as $a)
              <a class="ag-evt {{ $a->categorie }}" href="{{ route('site.activite', $a) }}" title="{{ $a->titre }}">{{ $a->titre }}</a>
            @endforeach
          </div>
        @endforeach
      </div>
    </div>

    <div class="section-head" style="margin-top:36px"><h2>Ce mois-ci</h2></div>
    @if ($activites->isEmpty())
      <p class="empty">Aucune activité prévue ce mois-ci.</p>
    @else
      <div class="grid">@foreach ($activites as $a) @include('site._carte', ['a' => $a]) @endforeach</div>
    @endif
  </div>
</section>
@endsection
