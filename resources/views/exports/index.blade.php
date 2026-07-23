@extends('layouts.app')

@section('title', 'Exports')
@section('page-title', 'Centre d\'Exports')
@section('breadcrumb', 'Accueil / Exports')

@section('content')

  <div class="export-section">
    <div class="export-text">
      <h3><i class="fas fa-file-export"></i> Exporter la liste des visiteurs</h3>
      <p>Choisissez une période et un format, le fichier se télécharge immédiatement</p>
    </div>
  </div>

  <form method="GET" action="{{ route('visiteurs.export') }}" class="table-card" style="padding:1.5rem;margin-top:1.25rem;margin-bottom:1.75rem;">
    <div class="filter-grid" style="grid-template-columns:repeat(4,1fr);">
      <div class="filter-group">
        <label>Type</label>
        <select name="type">
          <option value="">Tous</option>
          <option value="Élève">Élèves</option>
          <option value="Fonctionnaire">Fonctionnaires</option>
          <option value="Externe">Externes</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Genre</label>
        <select name="sexe">
          <option value="">Tous</option>
          <option value="M">Masculin</option>
          <option value="F">Féminin</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Date précise</label>
        <input type="date" name="date"/>
      </div>
      <div class="filter-group">
        <label>Format</label>
        <select name="format">
          <option value="csv">CSV</option>
          <option value="xlsx">Excel (.xlsx)</option>
          <option value="pdf">PDF</option>
        </select>
      </div>
    </div>
    <div style="margin-top:1.25rem;">
      <button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Générer et télécharger</button>
    </div>
  </form>

  <div class="table-card">
    <div class="table-card-header">
      <div class="table-info"><div class="title">Historique des exports</div><div class="count">15 derniers exports</div></div>
    </div>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr><th>Date</th><th>Utilisateur</th><th>Module</th><th>Format</th><th>Filtres</th></tr>
        </thead>
        <tbody>
          @forelse ($historique as $h)
            <tr>
              <td style="white-space:nowrap;">{{ $h->date_generation->format('d/m/Y H:i') }}</td>
              <td>{{ $h->user->name ?? 'Système' }}</td>
              <td>{{ ucfirst($h->module) }}</td>
              <td><span class="badge badge-blue">{{ strtoupper($h->format) }}</span></td>
              <td style="font-size:0.78rem;color:var(--muted);">
                @if (! empty($h->filtres))
                  {{ collect($h->filtres)->map(fn($v, $k) => "$k: $v")->implode(', ') }}
                @else
                  Aucun filtre
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:2rem;">Aucun export généré pour le moment.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

@endsection
