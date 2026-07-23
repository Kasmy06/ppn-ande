@extends('layouts.app')

@section('title', 'Paramètres')
@section('page-title', 'Paramètres')
@section('breadcrumb', 'Accueil / Paramètres / Journal')

@section('content')

  @include('parametres._tabs', ['active' => 'journal'])

  <form method="GET" action="{{ route('parametres.journal.index') }}" class="toolbar">
    <select name="user_id" onchange="this.form.submit()">
      <option value="">Tous les utilisateurs</option>
      @foreach ($users as $u)
        <option value="{{ $u->id }}" {{ (string) ($filters['user_id'] ?? '') === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
      @endforeach
    </select>
    <div class="search-wrap">
      <i class="fas fa-search"></i>
      <input class="search-input" type="text" name="action" value="{{ $filters['action'] ?? '' }}" placeholder="Filtrer par type d'action..."/>
    </div>
    <button type="button" class="btn btn-outline" onclick="window.location.href='{{ route('parametres.journal.index') }}'"><i class="fas fa-rotate-left"></i> Réinitialiser</button>
  </form>

  <div class="table-card">
    <div class="table-card-header">
      <div class="table-info"><div class="title">Journal d'activité</div><div class="count">{{ $journal->total() }} entrée(s)</div></div>
    </div>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr><th>Date</th><th>Utilisateur</th><th>Action</th><th>Détail</th></tr>
        </thead>
        <tbody>
          @forelse ($journal as $entry)
            <tr>
              <td style="white-space:nowrap;">{{ $entry->created_at->format('d/m/Y H:i') }}</td>
              <td>{{ $entry->user->name ?? 'Système' }}</td>
              <td><span class="badge badge-blue">{{ $entry->action }}</span></td>
              <td>{{ $entry->detail ?? '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:2rem;">Aucune activité enregistrée.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pagination-wrap">
      <div class="pagination-info">Page {{ $journal->currentPage() }} sur {{ max($journal->lastPage(), 1) }}</div>
      {{ $journal->links('pagination.custom') }}
    </div>
  </div>

@endsection
