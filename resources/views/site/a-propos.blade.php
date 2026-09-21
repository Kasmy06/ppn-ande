@extends('layouts.site')

@section('title', 'À propos')
@section('description', "Présentation du Point de Présence Numérique d'Andé : notre mission, nos services et nos horaires.")

@php
  use App\Support\SiteInfo;
  $texte = SiteInfo::aPropos();
  $horaires = SiteInfo::horaires();
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
@endsection
