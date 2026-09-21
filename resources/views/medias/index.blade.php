@extends('layouts.app')

@section('title', 'Photos & vidéos')
@section('page-title', 'Photos & vidéos du site')
@section('breadcrumb', 'Accueil / Photos & vidéos')

@section('content')
  <div class="toolbar">
    <a class="btn btn-outline" href="{{ route('site.galerie') }}" target="_blank"><i class="fas fa-up-right-from-square"></i> Voir la galerie</a>
    <a class="btn btn-primary" href="{{ route('medias.create') }}"><i class="fas fa-plus"></i> Ajouter un média</a>
  </div>

  <div class="table-card">
    <div style="overflow-x:auto;">
      <table>
        <thead><tr><th>Aperçu</th><th>Titre</th><th>Type</th><th>Activité liée</th><th>Statut</th><th>Actions</th></tr></thead>
        <tbody>
          @forelse ($medias as $m)
            <tr>
              <td>
                @if ($m->apercu_url)
                  <img src="{{ $m->apercu_url }}" alt="" style="width:64px;height:48px;object-fit:cover;border-radius:8px;"/>
                @else
                  <i class="fas fa-video" style="font-size:1.4rem;color:var(--muted)"></i>
                @endif
              </td>
              <td><strong>{{ $m->titre }}</strong></td>
              <td><span class="badge badge-{{ $m->type === 'photo' ? 'blue' : 'purple' }}">{{ $m->type === 'photo' ? 'Photo' : 'Vidéo' }}</span></td>
              <td>{{ $m->activite?->titre ?? '—' }}</td>
              <td>
                @if (auth()->user()->isSuperAdmin())
                  <form method="POST" action="{{ route('medias.publication', $m) }}" style="display:inline">
                    @csrf @method('PATCH')
                    <button class="btn btn-sm {{ $m->publie ? 'btn-outline' : 'btn-primary' }}" title="{{ $m->publie ? 'Retirer de la galerie publique' : 'Rendre visible dans la galerie publique' }}">
                      <i class="fas {{ $m->publie ? 'fa-eye' : 'fa-eye-slash' }}"></i> {{ $m->publie ? 'Publié · Retirer' : 'Brouillon · Publier' }}
                    </button>
                  </form>
                @else
                  <span class="badge badge-{{ $m->publie ? 'green' : 'orange' }}">{{ $m->publie ? 'Publié' : 'En attente de validation' }}</span>
                @endif
              </td>
              <td>
                @if (auth()->user()->isSuperAdmin())
                <div class="action-btns">
                  <a class="btn btn-sm btn-outline" title="Modifier" href="{{ route('medias.edit', $m) }}"><i class="fas fa-pen"></i></a>
                  <form method="POST" action="{{ route('medias.destroy', $m) }}" onsubmit="return confirm('Supprimer ce média ?')" style="display:inline">
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
            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem;">Aucun média. Ajoutez la première photo ou vidéo !</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="pagination-wrap">
      <div class="pagination-info">Page {{ $medias->currentPage() }} sur {{ max($medias->lastPage(), 1) }}</div>
      {{ $medias->links('pagination.custom') }}
    </div>
  </div>
@endsection
