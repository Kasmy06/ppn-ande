@extends('layouts.app')

@php
  $editingId = old('etablissement_id');
  $modalAction = $editingId ? route('etablissements.update', $editingId) : route('etablissements.store');
  $modalMethod = $editingId ? 'PUT' : 'POST';
  $typeLabels = ['scolaire' => 'Scolaire', 'collectivite' => 'Collectivité', 'association' => 'Association', 'autre' => 'Autre'];
  $typeBadges = ['scolaire' => 'blue', 'collectivite' => 'orange', 'association' => 'green', 'autre' => 'purple'];
@endphp

@section('title', 'Établissements')
@section('page-title', 'Établissements Partenaires')
@section('breadcrumb', 'Accueil / Établissements')

@section('content')

  <form method="GET" action="{{ route('etablissements.index') }}" class="toolbar" id="filterForm">
    <div class="search-wrap">
      <i class="fas fa-search"></i>
      <input class="search-input" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Rechercher par nom, ville..."/>
    </div>
    <select name="type" onchange="document.getElementById('filterForm').submit()">
      <option value="">Tous les types</option>
      @foreach ($typeLabels as $value => $label)
        <option value="{{ $value }}" {{ ($filters['type'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
    <button type="button" class="btn btn-outline" onclick="window.location.href='{{ route('etablissements.index') }}'"><i class="fas fa-rotate-left"></i> Réinitialiser</button>
    @if (auth()->user()->isSuperAdmin())
      <button type="button" class="btn btn-primary" onclick="openCreateModal()"><i class="fas fa-plus"></i> Ajouter établissement</button>
    @endif
  </form>

  <div class="table-card">
    <div class="table-card-header">
      <div class="table-info">
        <div class="title">Annuaire des Établissements</div>
        <div class="count">Affichage de {{ $etablissements->firstItem() ?? 0 }}–{{ $etablissements->lastItem() ?? 0 }} sur {{ $etablissements->total() }} entrées</div>
      </div>
    </div>

    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Type</th>
            <th>Ville</th>
            <th>Contact</th>
            <th>Visiteurs</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($etablissements as $e)
            <tr>
              <td><strong>{{ $e->nom }}</strong></td>
              <td><span class="badge badge-{{ $typeBadges[$e->type] }}">{{ $typeLabels[$e->type] }}</span></td>
              <td>{{ $e->ville ?? '—' }}</td>
              <td>
                @if ($e->contact_nom || $e->contact_email)
                  {{ $e->contact_nom }}<br>
                  <span style="color:var(--muted);font-size:0.78rem;">{{ $e->contact_email }}</span>
                @else
                  —
                @endif
              </td>
              <td><span class="badge badge-blue">{{ $e->visiteurs_count }}</span></td>
              <td>
                @if (auth()->user()->isSuperAdmin())
                  <div class="action-btns">
                    <button type="button" class="btn btn-sm btn-outline" title="Modifier"
                      onclick="openEditModal({
                        action: '{{ route('etablissements.update', $e->id) }}',
                        id: {{ $e->id }},
                        nom: {{ Js::from($e->nom) }},
                        type: {{ Js::from($e->type) }},
                        adresse: {{ Js::from($e->adresse) }},
                        ville: {{ Js::from($e->ville) }},
                        code_postal: {{ Js::from($e->code_postal) }},
                        contact_nom: {{ Js::from($e->contact_nom) }},
                        contact_telephone: {{ Js::from($e->contact_telephone) }},
                        contact_email: {{ Js::from($e->contact_email) }}
                      })">
                      <i class="fas fa-pen"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" title="Supprimer"
                      onclick="confirmDelete('{{ route('etablissements.destroy', $e->id) }}', {{ Js::from($e->nom) }})">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                @else
                  <span style="color:var(--muted);font-size:0.78rem;">Lecture seule</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem;">Aucun établissement trouvé.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="pagination-wrap">
      <div class="pagination-info">Page {{ $etablissements->currentPage() }} sur {{ max($etablissements->lastPage(), 1) }}</div>
      {{ $etablissements->links('pagination.custom') }}
    </div>
  </div>

  @if (auth()->user()->isSuperAdmin())
  <!-- MODAL AJOUT/EDITION -->
  <div class="modal-overlay" id="modalOverlay">
    <div class="modal">
      <form method="POST" action="{{ $modalAction }}" id="etabForm">
        @csrf
        <input type="hidden" name="_method" id="methodField" value="{{ $modalMethod }}">
        <input type="hidden" name="etablissement_id" id="fId" value="{{ $editingId }}">

        <div class="modal-header">
          <div class="modal-title" id="modalTitle">{{ $editingId ? 'Modifier l\'Établissement' : 'Ajouter un Établissement' }}</div>
          <button type="button" class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label>Nom *</label>
              <input class="form-control" type="text" name="nom" id="fNom" value="{{ old('nom') }}" placeholder="Ex: École Jean Moulin" required/>
              @error('nom') <div class="field-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
              <label>Type *</label>
              <select class="form-control" name="type" id="fType" required>
                @foreach ($typeLabels as $value => $label)
                  <option value="{{ $value }}" {{ old('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
              @error('type') <div class="field-error">{{ $message }}</div> @enderror
            </div>
          </div>
          <div class="form-group">
            <label>Adresse</label>
            <input class="form-control" type="text" name="adresse" id="fAdresse" value="{{ old('adresse') }}" placeholder="Ex: 12 rue de la Mairie"/>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Ville</label>
              <input class="form-control" type="text" name="ville" id="fVille" value="{{ old('ville') }}" placeholder="Ex: Andé"/>
            </div>
            <div class="form-group">
              <label>Code postal</label>
              <input class="form-control" type="text" name="code_postal" id="fCp" value="{{ old('code_postal') }}" placeholder="Ex: 27430"/>
            </div>
          </div>
          <div class="form-group">
            <label>Contact référent</label>
            <input class="form-control" type="text" name="contact_nom" id="fContactNom" value="{{ old('contact_nom') }}" placeholder="Ex: Sylvie Renard"/>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Téléphone du contact</label>
              <input class="form-control" type="text" name="contact_telephone" id="fContactTel" value="{{ old('contact_telephone') }}" placeholder="Ex: 02 32 00 00 00"/>
            </div>
            <div class="form-group">
              <label>Email du contact</label>
              <input class="form-control" type="email" name="contact_email" id="fContactEmail" value="{{ old('contact_email') }}" placeholder="Ex: contact@etablissement.fr"/>
              @error('contact_email') <div class="field-error">{{ $message }}</div> @enderror
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
  <div class="modal-overlay" id="confirmOverlay">
    <div class="confirm-dialog">
      <div class="confirm-icon"><i class="fas fa-trash-can"></i></div>
      <div class="confirm-title">Confirmer la suppression</div>
      <p class="confirm-text" id="confirmText">Cette action est irréversible.</p>
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
    document.getElementById('modalTitle').textContent = 'Ajouter un Établissement';
    const form = document.getElementById('etabForm');
    form.action = "{{ route('etablissements.store') }}";
    document.getElementById('methodField').value = 'POST';
    document.getElementById('fId').value = '';
    form.reset();
    openModal();
  }

  function openEditModal(e) {
    document.getElementById('modalTitle').textContent = 'Modifier l\'Établissement';
    const form = document.getElementById('etabForm');
    form.action = e.action;
    document.getElementById('methodField').value = 'PUT';
    document.getElementById('fId').value = e.id;
    document.getElementById('fNom').value = e.nom;
    document.getElementById('fType').value = e.type;
    document.getElementById('fAdresse').value = e.adresse || '';
    document.getElementById('fVille').value = e.ville || '';
    document.getElementById('fCp').value = e.code_postal || '';
    document.getElementById('fContactNom').value = e.contact_nom || '';
    document.getElementById('fContactTel').value = e.contact_telephone || '';
    document.getElementById('fContactEmail').value = e.contact_email || '';
    openModal();
  }

  function confirmDelete(url, name) {
    document.getElementById('confirmText').textContent = `L'établissement "${name}" sera définitivement supprimé.`;
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
