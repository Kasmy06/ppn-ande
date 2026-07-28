@extends('layouts.app')

@section('title', "$prenom $nom")
@section('page-title', 'Fiche Visiteur')
@section('breadcrumb', 'Accueil / Visiteurs / Fiche')

@section('content')

  <a href="{{ route('visiteurs.index') }}" style="display:inline-flex;align-items:center;gap:0.4rem;font-size:0.85rem;color:var(--muted);text-decoration:none;margin-bottom:1.25rem;">
    <i class="fas fa-arrow-left"></i> Retour à la liste des visiteurs
  </a>

  <div class="table-card" style="padding:1.75rem;margin-bottom:1.75rem;display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
    <div class="mini-avatar" style="width:64px;height:64px;font-size:1.5rem;background:#f6ecf8;color:#8E2E8E;">
      {{ mb_strtoupper(mb_substr($prenom, 0, 1).mb_substr($nom, 0, 1)) }}
    </div>
    <div style="flex:1;min-width:200px;">
      <div style="font-family:'Syne',sans-serif;font-size:1.3rem;font-weight:700;">{{ $prenom }} {{ $nom }}</div>
      <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-top:0.5rem;">
        @if ($sexe === 'M')
          <span class="badge badge-male"><i class="fas fa-mars"></i> Masculin</span>
        @else
          <span class="badge badge-female"><i class="fas fa-venus"></i> Féminin</span>
        @endif
        @foreach ($types as $type)
          <span class="badge badge-{{ $type === 'Élève' ? 'blue' : ($type === 'Fonctionnaire' ? 'orange' : 'green') }}">{{ $type }}</span>
        @endforeach
      </div>
      @if ($etablissements->isNotEmpty())
        <div style="font-size:0.82rem;color:var(--muted);margin-top:0.5rem;">
          <i class="fas fa-school"></i> {{ $etablissements->implode(' · ') }}
        </div>
      @endif
      @if ($telephone || $email)
        <div style="font-size:0.82rem;color:var(--muted);margin-top:0.35rem;display:flex;gap:1.25rem;flex-wrap:wrap;">
          @if ($telephone)
            <span><i class="fas fa-phone"></i> {{ $telephone }}</span>
          @endif
          @if ($email)
            <span><i class="fas fa-envelope"></i> {{ $email }}</span>
          @endif
        </div>
      @endif
    </div>
  </div>

  <div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.75rem;">
    <div class="stat-card blue">
      <div class="stat-top"><div class="stat-icon"><i class="fas fa-calendar-check"></i></div></div>
      <div class="stat-num">{{ $visites->count() }}</div>
      <div class="stat-label">Visite{{ $visites->count() > 1 ? 's' : '' }} au total</div>
    </div>
    <div class="stat-card green">
      <div class="stat-top"><div class="stat-icon"><i class="fas fa-calendar-plus"></i></div></div>
      <div class="stat-num" style="font-size:1.3rem;">{{ $premiereVisite->date_visite->format('d/m/Y') }}</div>
      <div class="stat-label">Première visite</div>
    </div>
    <div class="stat-card orange">
      <div class="stat-top"><div class="stat-icon"><i class="fas fa-calendar-day"></i></div></div>
      <div class="stat-num" style="font-size:1.3rem;">{{ $derniereVisite->date_visite->format('d/m/Y') }}</div>
      <div class="stat-label">Dernière visite</div>
    </div>
  </div>

  <div class="table-card">
    <div class="table-card-header">
      <div class="table-info">
        <div class="title">Historique des visites</div>
        <div class="count">{{ $visites->count() }} visite{{ $visites->count() > 1 ? 's' : '' }} enregistrée{{ $visites->count() > 1 ? 's' : '' }}</div>
      </div>
    </div>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr><th>Date</th><th>Heure</th><th>Type</th><th>Établissement</th><th>Classe / Poste</th></tr>
        </thead>
        <tbody>
          @foreach ($visites as $v)
            <tr>
              <td>{{ $v->date_visite->format('d/m/Y') }}</td>
              <td>{{ $v->heureFormatee ?? '—' }}</td>
              <td><span class="badge badge-{{ $v->type === 'Élève' ? 'blue' : ($v->type === 'Fonctionnaire' ? 'orange' : 'green') }}">{{ $v->type }}</span></td>
              <td>{{ $v->etablissement->nom ?? '—' }}</td>
              <td>{{ $v->classe_ou_poste ?? '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

@endsection
