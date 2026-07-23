@extends('layouts.app')

@php
  $editingId = old('visiteur_id');
  $modalAction = $editingId ? route('visiteurs.update', $editingId) : route('visiteurs.store');
  $modalMethod = $editingId ? 'PUT' : 'POST';
  $sort = $filters['sort'] ?? 'date_visite';
  $dir = $filters['dir'] ?? 'desc';
  $nextDir = fn($col) => ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
  $sortIcon = fn($col) => $sort !== $col ? 'fa-sort' : ($dir === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
@endphp

@section('title', 'Gestion des Visiteurs')
@section('page-title', 'Gestion des Visiteurs')
@section('breadcrumb', 'Accueil / Visiteurs')

@section('content')

  <form method="GET" action="{{ route('visiteurs.index') }}" class="toolbar" id="filterForm">
    <div class="search-wrap">
      <i class="fas fa-search"></i>
      <input class="search-input" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Rechercher par nom, établissement..."/>
    </div>
    <select name="type" onchange="document.getElementById('filterForm').submit()">
      <option value="">Tous les types</option>
      <option value="Élève" {{ ($filters['type'] ?? '') === 'Élève' ? 'selected' : '' }}>Élève</option>
      <option value="Fonctionnaire" {{ ($filters['type'] ?? '') === 'Fonctionnaire' ? 'selected' : '' }}>Fonctionnaire</option>
      <option value="Externe" {{ ($filters['type'] ?? '') === 'Externe' ? 'selected' : '' }}>Externe</option>
    </select>
    <select name="sexe" onchange="document.getElementById('filterForm').submit()">
      <option value="">Tous les genres</option>
      <option value="M" {{ ($filters['sexe'] ?? '') === 'M' ? 'selected' : '' }}>Masculin</option>
      <option value="F" {{ ($filters['sexe'] ?? '') === 'F' ? 'selected' : '' }}>Féminin</option>
    </select>
    <input type="date" name="date" value="{{ $filters['date'] ?? '' }}" onchange="document.getElementById('filterForm').submit()" title="Filtrer par date"/>
    <button type="button" class="btn btn-outline" onclick="window.location.href='{{ route('visiteurs.index') }}'"><i class="fas fa-rotate-left"></i> Réinitialiser</button>
    <button type="button" class="btn btn-primary" onclick="openCreateModal()"><i class="fas fa-plus"></i> Ajouter visiteur</button>
  </form>

  <div class="table-card">
    <div class="table-card-header">
      <div class="table-info">
        <div class="title">Liste des Visiteurs</div>
        <div class="count">Affichage de {{ $visiteurs->firstItem() ?? 0 }}–{{ $visiteurs->lastItem() ?? 0 }} sur {{ $visiteurs->total() }} entrées</div>
      </div>
      <div class="action-btns">
        <a href="{{ route('visiteurs.export', request()->query() + ['format' => 'csv']) }}" class="btn btn-outline btn-sm" title="Export CSV"><i class="fas fa-file-csv"></i> CSV</a>
        <a href="{{ route('visiteurs.export', request()->query() + ['format' => 'xlsx']) }}" class="btn btn-outline btn-sm" title="Export Excel"><i class="fas fa-file-excel"></i> Excel</a>
        <a href="{{ route('visiteurs.export', request()->query() + ['format' => 'pdf']) }}" class="btn btn-outline btn-sm" title="Export PDF"><i class="fas fa-file-pdf"></i> PDF</a>
      </div>
    </div>

    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr>
            <th class="sortable" onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => 'nom', 'dir' => $nextDir('nom')]) }}'">Nom <i class="fas {{ $sortIcon('nom') }}"></i></th>
            <th class="sortable" onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => 'sexe', 'dir' => $nextDir('sexe')]) }}'">Genre <i class="fas {{ $sortIcon('sexe') }}"></i></th>
            <th class="sortable" onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => 'type', 'dir' => $nextDir('type')]) }}'">Type <i class="fas {{ $sortIcon('type') }}"></i></th>
            <th>Établissement</th>
            <th class="sortable" onclick="window.location.href='{{ request()->fullUrlWithQuery(['sort' => 'date_visite', 'dir' => $nextDir('date_visite')]) }}'">Date <i class="fas {{ $sortIcon('date_visite') }}"></i></th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($visiteurs as $v)
            <tr>
              <td>
                <div class="avatar-name">
                  <div class="mini-avatar" style="background:#dbeafe;color:#1d4ed8;">{{ $v->initiales }}</div>
                  <strong>{{ $v->prenom }} {{ $v->nom }}</strong>
                </div>
              </td>
              <td>
                @if ($v->sexe === 'M')
                  <span class="badge badge-male"><i class="fas fa-mars"></i> M</span>
                @else
                  <span class="badge badge-female"><i class="fas fa-venus"></i> F</span>
                @endif
              </td>
              <td><span class="badge badge-{{ $v->type === 'Élève' ? 'blue' : ($v->type === 'Fonctionnaire' ? 'orange' : 'green') }}">{{ $v->type }}</span></td>
              <td>{{ $v->etablissement->nom ?? '—' }}</td>
              <td>{{ $v->date_visite->format('d/m/Y') }}</td>
              <td>
                <div class="action-btns">
                  <button type="button" class="btn btn-sm btn-outline" title="Modifier"
                    onclick="openEditModal({
                      action: '{{ route('visiteurs.update', $v->id) }}',
                      id: {{ $v->id }},
                      prenom: {{ Js::from($v->prenom) }},
                      nom: {{ Js::from($v->nom) }},
                      sexe: {{ Js::from($v->sexe) }},
                      type: {{ Js::from($v->type) }},
                      etablissement_id: {{ Js::from($v->etablissement_id) }},
                      classe_ou_poste: {{ Js::from($v->classe_ou_poste) }},
                      date_visite: {{ Js::from($v->date_visite->format('Y-m-d')) }}
                    })">
                    <i class="fas fa-pen"></i>
                  </button>
                  @if (auth()->user()->isSuperAdmin())
                    <button type="button" class="btn btn-sm btn-danger" title="Supprimer"
                      onclick="confirmDelete('{{ route('visiteurs.destroy', $v->id) }}', {{ Js::from($v->prenom.' '.$v->nom) }})">
                      <i class="fas fa-trash"></i>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem;">Aucun visiteur trouvé.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pagination-wrap">
      <div class="pagination-info">Page {{ $visiteurs->currentPage() }} sur {{ max($visiteurs->lastPage(), 1) }}</div>
      {{ $visiteurs->links('pagination.custom') }}
    </div>
  </div>

  <!-- MODAL AJOUT/EDITION -->
  <div class="modal-overlay" id="modalOverlay">
    <div class="modal">
      <form method="POST" action="{{ $modalAction }}" id="visiteurForm">
        @csrf
        <input type="hidden" name="_method" id="methodField" value="{{ $modalMethod }}">
        <input type="hidden" name="visiteur_id" id="fId" value="{{ $editingId }}">

        <div class="modal-header">
          <div class="modal-title" id="modalTitle">{{ $editingId ? 'Modifier le Visiteur' : 'Ajouter un Visiteur' }}</div>
          <button type="button" class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label>Prénom *</label>
              <input class="form-control" type="text" name="prenom" id="fPrenom" value="{{ old('prenom') }}" placeholder="Ex: Marie" required/>
              @error('prenom') <div class="field-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
              <label>Nom *</label>
              <input class="form-control" type="text" name="nom" id="fNom" value="{{ old('nom') }}" placeholder="Ex: Dupont" required/>
              @error('nom') <div class="field-error">{{ $message }}</div> @enderror
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Genre *</label>
              <select class="form-control" name="sexe" id="fSexe" required>
                <option value="">Sélectionner</option>
                <option value="M" {{ old('sexe') === 'M' ? 'selected' : '' }}>Masculin</option>
                <option value="F" {{ old('sexe') === 'F' ? 'selected' : '' }}>Féminin</option>
              </select>
              @error('sexe') <div class="field-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
              <label>Type *</label>
              <select class="form-control" name="type" id="fType" required>
                <option value="">Sélectionner</option>
                <option value="Élève" {{ old('type') === 'Élève' ? 'selected' : '' }}>Élève</option>
                <option value="Fonctionnaire" {{ old('type') === 'Fonctionnaire' ? 'selected' : '' }}>Fonctionnaire</option>
                <option value="Externe" {{ old('type') === 'Externe' ? 'selected' : '' }}>Externe</option>
              </select>
              @error('type') <div class="field-error">{{ $message }}</div> @enderror
            </div>
          </div>
          <div class="form-group">
            <label>Établissement</label>
            <select class="form-control" name="etablissement_id" id="fEtab">
              <option value="">Aucun / Non renseigné</option>
              @foreach ($etablissements as $e)
                <option value="{{ $e->id }}" {{ (string) old('etablissement_id') === (string) $e->id ? 'selected' : '' }}>{{ $e->nom }}</option>
              @endforeach
            </select>
            @error('etablissement_id') <div class="field-error">{{ $message }}</div> @enderror
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Date de visite *</label>
              <input class="form-control" type="date" name="date_visite" id="fDate" value="{{ old('date_visite', now()->format('Y-m-d')) }}" required/>
              @error('date_visite') <div class="field-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
              <label>Classe / Poste</label>
              <input class="form-control" type="text" name="classe_ou_poste" id="fClasse" value="{{ old('classe_ou_poste') }}" placeholder="Ex: CM2 / Inspecteur"/>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeModal()">Annuler</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <!-- CONFIRM DELETE -->
  @if (auth()->user()->isSuperAdmin())
  <div class="modal-overlay" id="confirmOverlay">
    <div class="confirm-dialog">
      <div class="confirm-icon"><i class="fas fa-trash-can"></i></div>
      <div class="confirm-title">Confirmer la suppression</div>
      <p class="confirm-text" id="confirmText">Cette action est irréversible. Le visiteur sera définitivement supprimé.</p>
      <div class="confirm-btns">
        <button type="button" class="btn btn-outline" onclick="closeConfirm()">Annuler</button>
        <button type="button" class="btn btn-red" id="confirmDeleteBtn"><i class="fas fa-trash"></i> Supprimer</button>
      </div>
    </div>
  </div>

  <form method="POST" id="deleteForm" style="display:none;">
    @csrf
    @method('DELETE')
  </form>
  @endif

@endsection

@push('scripts')
<script>
  function openModal() { document.getElementById('modalOverlay').classList.add('open'); }
  function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); }

  function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Ajouter un Visiteur';
    const form = document.getElementById('visiteurForm');
    form.action = "{{ route('visiteurs.store') }}";
    document.getElementById('methodField').value = 'POST';
    document.getElementById('fId').value = '';
    form.reset();
    document.getElementById('fDate').value = new Date().toISOString().split('T')[0];
    openModal();
  }

  function openEditModal(v) {
    document.getElementById('modalTitle').textContent = 'Modifier le Visiteur';
    const form = document.getElementById('visiteurForm');
    form.action = v.action;
    document.getElementById('methodField').value = 'PUT';
    document.getElementById('fId').value = v.id;
    document.getElementById('fPrenom').value = v.prenom;
    document.getElementById('fNom').value = v.nom;
    document.getElementById('fSexe').value = v.sexe;
    document.getElementById('fType').value = v.type;
    document.getElementById('fEtab').value = v.etablissement_id || '';
    document.getElementById('fClasse').value = v.classe_ou_poste || '';
    document.getElementById('fDate').value = v.date_visite;
    openModal();
  }

  function confirmDelete(url, name) {
    document.getElementById('confirmText').textContent = `Le visiteur "${name}" sera définitivement supprimé de la base de données.`;
    document.getElementById('deleteForm').action = url;
    document.getElementById('confirmOverlay').classList.add('open');
    document.getElementById('confirmDeleteBtn').onclick = () => document.getElementById('deleteForm').submit();
  }
  function closeConfirm() { document.getElementById('confirmOverlay').classList.remove('open'); }

  @if ($errors->any())
    document.addEventListener('DOMContentLoaded', () => openModal());
  @endif
</script>
@endpush
