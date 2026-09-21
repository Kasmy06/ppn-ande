@extends('layouts.site')

@section('title', 'Mentions légales')
@section('description', "Mentions légales du site du Point de Présence Numérique d'Andé.")

@php
  use App\Support\SiteInfo;
  $personnalise = SiteInfo::texte('mentions_legales');
@endphp

@section('content')
<section class="page-hero">
  <div class="wrap"><h1>Mentions légales</h1><p>Informations sur l'éditeur du site.</p></div>
</section>

<section class="section">
  <div class="wrap legal">
    @if ($personnalise)
      <div class="prose">{{ $personnalise }}</div>
    @else
      <h2>Éditeur du site</h2>
      <p><strong>{{ SiteInfo::nom() }}</strong> — Point de Présence Numérique<br>
        @if ($adresse = SiteInfo::adresse()){{ $adresse }}<br>@endif
        @if ($mail = SiteInfo::email())E-mail : <a href="mailto:{{ $mail }}">{{ $mail }}</a><br>@endif
        @foreach (SiteInfo::telephones() as $tel)Téléphone : <a href="{{ SiteInfo::lienTel($tel) }}">{{ $tel }}</a><br>@endforeach
      </p>
      @if ($responsable = SiteInfo::responsable())
        <p>Directeur de la publication : {{ $responsable }}</p>
      @endif

      @if ($hebergeur = SiteInfo::hebergeur())
        <h2>Hébergement</h2>
        <p>{{ $hebergeur }}</p>
      @endif

      <h2>Propriété intellectuelle</h2>
      <p>Les textes, photographies, vidéos, logos et autres éléments de ce site sont la propriété de {{ SiteInfo::nom() }} ou utilisés avec l'accord
        de leurs auteurs. Toute reproduction ou réutilisation sans autorisation préalable est interdite.</p>

      <h2>Responsabilité</h2>
      <p>Les informations publiées (activités, dates, horaires) sont mises à jour régulièrement mais peuvent évoluer : nous vous invitons à nous
        contacter pour les confirmer. {{ SiteInfo::nom() }} n'est pas responsable du contenu des sites externes vers lesquels ce site pourrait renvoyer.</p>

      <h2>Données personnelles</h2>
      <p>Le traitement des données personnelles est décrit dans notre
        @if (SiteInfo::page('confidentialite'))<a href="{{ route('site.confidentialite') }}">politique de confidentialité</a>@else politique de confidentialité @endif.</p>
    @endif
  </div>
</section>
@endsection
