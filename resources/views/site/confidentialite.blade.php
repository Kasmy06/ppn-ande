@extends('layouts.site')

@section('title', 'Politique de confidentialité')
@section('description', "Comment le Point de Présence Numérique d'Andé traite les données personnelles collectées via son site.")

@php
  use App\Support\SiteInfo;
  $personnalise = SiteInfo::texte('confidentialite');
  $contacts = array_filter([SiteInfo::email(), ...SiteInfo::telephones()]);
@endphp

@section('content')
<section class="page-hero">
  <div class="wrap"><h1>Politique de confidentialité</h1><p>Vos données personnelles et vos droits.</p></div>
</section>

<section class="section">
  <div class="wrap legal">
    @if ($personnalise)
      <div class="prose">{{ $personnalise }}</div>
    @else
      <h2>1. Qui est responsable de vos données ?</h2>
      <p>{{ SiteInfo::nom() }}@if ($adresse = SiteInfo::adresse()), {{ $adresse }}@endif.
        @if ($responsable = SiteInfo::responsable())Responsable du traitement : {{ $responsable }}.@endif
        @if ($contacts)Pour toute question : {{ implode(' · ', $contacts) }}.@endif</p>

      <h2>2. Quelles données collectons-nous ?</h2>
      <p>Le site public ne collecte des données personnelles que si vous nous écrivez par le <strong>formulaire de contact</strong> :
        votre nom, votre adresse e-mail, votre numéro de téléphone (facultatif), le sujet et le contenu de votre message.
        Aucune autre donnée personnelle n'est demandée aux visiteurs du site, et il n'y a ni publicité ni outil de suivi individuel.
        Le site affiche un <strong>compteur de visites anonyme</strong> : il additionne le nombre de visites par jour, sans jamais
        enregistrer d'adresse IP ni d'identifiant de visiteur. Une même session n'est comptée qu'une fois par jour.</p>

      <h2>3. Pourquoi et sur quelle base ?</h2>
      <p>Ces données servent uniquement à <strong>répondre à votre demande</strong>. Elles sont traitées avec votre consentement,
        que vous exprimez en cochant la case prévue avant l'envoi du formulaire.</p>

      <h2>4. Qui y a accès ?</h2>
      <p>Seuls les membres habilités de l'équipe du PPN consultent les messages, depuis un espace protégé par identifiant et mot de passe.
        Vos données ne sont ni vendues ni cédées à des tiers.</p>

      <h2>5. Cookies et services externes</h2>
      <p>Le site n'utilise qu'un cookie technique indispensable à son fonctionnement et à sa sécurité (session), qui ne sert pas à vous suivre :
        il permet aussi de ne compter qu'une fois par jour une même visite, sans rien conserver de plus.
        Pour s'afficher, les pages chargent des polices de caractères (Google Fonts) et des icônes (cdnjs) depuis des serveurs tiers, qui peuvent
        recevoir votre adresse IP. Si une carte Google Maps ou une vidéo YouTube/Vimeo est affichée, ces services appliquent leurs propres règles.</p>

      <h2>6. Combien de temps les conservons-nous ?</h2>
      <p>Les messages sont conservés {{ SiteInfo::dureeConservation() }} après le dernier échange, puis supprimés.</p>

      <h2>7. Quels sont vos droits ?</h2>
      <p>Conformément à la loi n° 2013-450 du 19 juin 2013 relative à la protection des données à caractère personnel (Côte d'Ivoire),
        vous disposez d'un droit d'accès, de rectification, d'opposition et de suppression de vos données. Pour l'exercer,
        écrivez-nous @if (SiteInfo::page('contact'))via la <a href="{{ route('site.contact') }}">page Contact</a>@else aux coordonnées ci-dessus @endif.
        Si vous estimez que vos droits ne sont pas respectés, vous pouvez saisir l'Autorité de régulation des télécommunications/TIC de
        Côte d'Ivoire (ARTCI), autorité de protection des données personnelles.</p>
    @endif
  </div>
</section>
@endsection
