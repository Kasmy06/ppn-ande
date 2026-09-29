@extends('layouts.app')

@section('title', 'Annonces du site')
@section('page-title', 'Annonces du site public')
@section('breadcrumb', 'Accueil / Annonces')

@section('content')
  <div class="toolbar">
    <a class="btn btn-outline" href="{{ route('site.accueil') }}" target="_blank"><i class="fas fa-up-right-from-square"></i> Voir le site</a>
    <a class="btn btn-primary" href="{{ route('annonces.create') }}"><i class="fas fa-plus"></i> Ajouter une annonce</a>
  </div>

  <div class="table-card">
    <div style="overflow-x:auto;">
      <table>
        <thead><tr><th>Titre</th><th>Urgente</th><th>Publication</th><th>Expiration</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
          @forelse ($annonces as $a)
            <tr>
              <td><strong>{{ $a->titre }}</strong></td>
              <td>@if ($a->urgente)<span class="badge badge-red"><i class="fas fa-triangle-exclamation"></i> Urgente</span>@else — @endif</td>
              <td>{{ $a->date_publication->format('d/m/Y') }}</td>
              <td>{{ $a->date_expiration?->format('d/m/Y') ?? '—' }}{{ $a->expiree ? ' (expirée)' : '' }}</td>
              <td>
                @if (auth()->user()->isSuperAdmin())
                  <form method="POST" action="{{ route('annonces.publication', $a) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button class="btn btn-sm {{ $a->publie ? 'btn-outline' : 'btn-primary' }}" title="{{ $a->publie ? 'Retirer du site public' : 'Rendre visible sur le site public' }}">
                      <i class="fas {{ $a->publie ? 'fa-eye' : 'fa-eye-slash' }}"></i> {{ $a->publie ? 'Publiée · Retirer' : 'Brouillon · Publier' }}
                    </button>
                  </form>
                @else
                  <span class="badge badge-{{ $a->publie ? 'green' : 'orange' }}">{{ $a->publie ? 'Publiée' : 'En attente de validation' }}</span>
                @endif
              </td>
              <td>
                @if (auth()->user()->isSuperAdmin())
                <div class="action-btns">
                  <a class="btn btn-sm btn-outline" title="Modifier" href="{{ route('annonces.edit', $a) }}"><i class="fas fa-pen"></i></a>
                  <form method="POST" action="{{ route('annonces.destroy', $a) }}" onsubmit="return confirm('Supprimer cette annonce ?')" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                  </form>
                </div>
                @else
                  <span style="color:var(--muted);font-size:0.78rem;">—</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem;">Aucune annonce. Ajoutez la première !</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pagination-wrap">
      <div class="pagination-info">Page {{ $annonces->currentPage() }} sur {{ max($annonces->lastPage(), 1) }}</div>
      {{ $annonces->links('pagination.custom') }}
    </div>
  </div>
@endsection
