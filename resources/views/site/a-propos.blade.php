@extends('layouts.site')

@section('title', 'À propos')
@section('description', "Présentation du Point de Présence Numérique d'Andé : notre mission, nos services et nos horaires.")

@php
  use App\Support\SiteInfo;
  $texte = SiteInfo::aPropos();
  $horaires = SiteInfo::horaires();
  // « Andé » est mis en valeur automatiquement s'il figure dans la liste des villes rurales.
  $villesRurales = preg_replace('/and[ée]/iu', '<strong style="color:var(--magenta)">$0</strong>', e(SiteInfo::reseauVillesRurales()));
@endphp

@section('content')
<section class="page-hero">
  <div class="wrap"><h1>À propos du PPN</h1><p>Le numérique à la portée de tous à Andé.</p></div>
</section>

<section class="section">
  <div class="wrap detail" style="padding:0">
    <div>
      @if ($texte)
        <div class="prose">{{ $texte }}</div>
      @else
        <div class="prose">Le Point de Présence Numérique (PPN) d'Andé est un lieu d'accès libre aux outils et aux usages du numérique.

Élèves, agents publics, associations et habitants y trouvent un espace équipé, des formations, des ateliers pratiques et un accompagnement personnalisé pour se former, réaliser leurs démarches en ligne et gagner en autonomie.</div>
      @endif

      <div class="features" style="margin-top:32px">
        <div class="feature"><i class="fas fa-graduation-cap"></i><h3>Se former</h3><p>Formations et ateliers pour tous les niveaux.</p></div>
        <div class="feature"><i class="fas fa-hand-holding-heart"></i><h3>Être accompagné</h3><p>Une aide individuelle pour vos démarches et vos usages.</p></div>
        <div class="feature"><i class="fas fa-users"></i><h3>Se rencontrer</h3><p>Événements et rencontres autour du numérique.</p></div>
      </div>
    </div>

    @if ($horaires || \App\Support\SiteInfo::page('contact'))
    <aside class="aside-box">
      <h3>Nous rejoindre</h3>
      @if ($horaires)
        <ul><li><i class="fas fa-clock"></i><div><span>Horaires</span><span style="white-space:pre-line;color:var(--text);font-size:.92rem">{{ $horaires }}</span></div></li></ul>
      @endif
      @if (\App\Support\SiteInfo::page('contact'))<a class="btn btn-green" href="{{ route('site.contact') }}" style="width:100%;justify-content:center"><i class="fas fa-envelope"></i> Nous contacter</a>@endif
    </aside>
    @endif
  </div>
</section>

<section class="section alt">
  <div class="wrap">
    <div class="section-head">
      <div><h2>Le réseau des PPN de l'UVCI</h2><p>Andé fait partie d'un réseau national de centres numériques de proximité.</p></div>
    </div>
    <p class="prose" style="max-width:760px">{{ SiteInfo::reseauTexte() }}</p>

    <div class="features" style="margin-top:28px">
      <div class="feature">
        <i class="fas fa-wifi"></i>
        <h3>Accès au numérique</h3>
        <p>Un espace de connexion Internet, de travail et de formation pour réduire la fracture numérique.</p>
      </div>
      <div class="feature">
        <i class="fas fa-route"></i>
        <h3>Proximité</h3>
        <p>Suivre ses cours en ligne, participer à des activités académiques ou recevoir une assistance sans avoir à se déplacer constamment vers Abidjan.</p>
      </div>
      <div class="feature">
        <i class="fas fa-seedling"></i>
        <h3>Développement local</h3>
        <p>Un relais communautaire — parfois associé à des radios locales ou des incubateurs technologiques — pour l'insertion professionnelle et l'innovation sociale.</p>
      </div>
    </div>

    <div class="feature" style="margin-top:28px;max-width:760px">
      <i class="fas fa-map-location-dot"></i>
      <h3>Où trouver un PPN ?</h3>
      <p style="margin-bottom:10px">Le réseau compte des centres aussi bien dans les grandes villes qu'en milieu rural.</p>
      <p><strong style="color:var(--text)">Abidjan et grandes villes :</strong> {{ SiteInfo::reseauVillesUrbaines() }}.</p>
      <p style="margin-top:6px"><strong style="color:var(--text)">Milieu rural :</strong> {!! $villesRurales !!}.</p>
    </div>
  </div>
</section>
@endsection
