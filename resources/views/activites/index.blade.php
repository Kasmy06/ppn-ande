@extends('layouts.app')

@section('title', 'Activités du site')
@section('page-title', 'Activités du site public')
@section('breadcrumb', 'Accueil / Activités')

@section('content')
  <div class="toolbar">
    <a class="btn btn-outline" href="{{ route('site.accueil') }}" target="_blank"><i class="fas fa-up-right-from-square"></i> Voir le site</a>
    <a class="btn btn-primary" href="{{ route('activites.create') }}"><i class="fas fa-plus"></i> Ajouter une activité</a>
  </div>

  <div class="table-card">
    <div style="overflow-x:auto;">
      <table>
        <thead><tr><th>Titre</th><th>Catégorie</th><th>Date</th><th>Lieu</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
          @forelse ($activites as $a)
            <tr>
              <td><strong>{{ $a->titre }}</strong></td>
              <td><span class="badge badge-purple">{{ $a->categorie_label }}</span></td>
              <td>{{ $a->date_debut->format('d/m/Y') }}{{ $a->date_fin ? ' → '.$a->date_fin->format('d/m/Y') : '' }}</td>
              <td>{{ $a->lieu ?? '—' }}</td>
              <td><span class="badge badge-{{ $a->publie ? 'green' : 'orange' }}">{{ $a->publie ? 'Publiée' : 'Brouillon' }}</span></td>
              <td>
                <div class="action-btns">
                  <a class="btn btn-sm btn-outline" title="Modifier" href="{{ route('activites.edit', $a) }}"><i class="fas fa-pen"></i></a>
                  <form method="POST" action="{{ route('activites.destroy', $a) }}" onsubmit="return confirm('Supprimer cette activité ?')" style="display:inline">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem;">Aucune activité. Ajoutez la première !</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pagination-wrap">
      <div class="pagination-info">Page {{ $activites->currentPage() }} sur {{ max($activites->lastPage(), 1) }}</div>
      {{ $activites->links('pagination.custom') }}
    </div>
  </div>
@endsection
