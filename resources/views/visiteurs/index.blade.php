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
                  <a href="{{ route('visiteurs.fiche', ['prenom' => $v->prenom, 'nom' => $v->nom, 'sexe' => $v->sexe]) }}" class="name-link">
                    {{ $v->prenom }} {{ $v->nom }}
                  </a>
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
              <td>
                {{ $v->date_visite->format('d/m/Y') }}
                @if ($v->heureFormatee)
                  <div style="font-size:0.72rem;color:var(--muted);"><i class="far fa-clock"></i> {{ $v->heureFormatee }}</div>
                @endif
              </td>
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
                      telephone: {{ Js::from($v->telephone) }},
                      email: {{ Js::from($v->email) }},
                      date_visite: {{ Js::from($v->date_visite->format('Y-m-d')) }},
                      heure_arrivee: {{ Js::from($v->heureFormatee) }}
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
          <div class="form-group" id="rechercheExistantWrap" style="{{ $editingId ? 'display:none;' : '' }}position:relative;">
            <label>Ce visiteur est-il déjà venu ?</label>
            <div class="input-wrap">
              <input class="form-control" type="text" id="fRecherche" placeholder="Tapez un nom pour retrouver un visiteur déjà enregistré..." autocomplete="off"/>
            </div>
            <div id="rechercheResultats" style="display:none;position:absolute;z-index:10;left:0;right:0;top:100%;margin-top:4px;background:white;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 25px rgba(0,0,0,0.1);max-height:220px;overflow-y:auto;"></div>
            <p style="font-size:0.75rem;color:var(--muted);margin-top:0.4rem;">Sélectionner un résultat pré-remplit sa fiche ci-dessous — il ne restera qu'à ajuster la date.</p>
          </div>
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
              <label>Heure d'arrivée *</label>
              <input class="form-control" type="time" name="heure_arrivee" id="fHeure" value="{{ old('heure_arrivee', now()->format('H:i')) }}" required/>
              @error('heure_arrivee') <div class="field-error">{{ $message }}</div> @enderror
            </div>
          </div>
          <div class="form-group">
            <label>Classe / Poste</label>
            <input class="form-control" type="text" name="classe_ou_poste" id="fClasse" value="{{ old('classe_ou_poste') }}" placeholder="Ex: CM2 / Inspecteur"/>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Téléphone</label>
              <input class="form-control" type="text" name="telephone" id="fTelephone" value="{{ old('telephone') }}" placeholder="Ex: 07 00 00 00 00"/>
              @error('telephone') <div class="field-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
              <label>Email</label>
              <input class="form-control" type="email" name="email" id="fEmail" value="{{ old('email') }}" placeholder="Ex: nom@exemple.fr"/>
              @error('email') <div class="field-error">{{ $message }}</div> @enderror
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
    document.getElementById('fHeure').value = new Date().toTimeString().slice(0, 5);
    document.getElementById('rechercheExistantWrap').style.display = '';
    document.getElementById('fRecherche').value = '';
    document.getElementById('rechercheResultats').style.display = 'none';
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
    document.getElementById('fTelephone').value = v.telephone || '';
    document.getElementById('fEmail').value = v.email || '';
    document.getElementById('fDate').value = v.date_visite;
    document.getElementById('fHeure').value = v.heure_arrivee || '';
    document.getElementById('rechercheExistantWrap').style.display = 'none';
    openModal();
  }

  // Recherche d'un visiteur déjà connu, pour pré-remplir le formulaire d'ajout.
  let rechercheTimer = null;
  document.getElementById('fRecherche').addEventListener('input', function () {
    const q = this.value.trim();
    clearTimeout(rechercheTimer);
    const resultatsBox = document.getElementById('rechercheResultats');

    if (q.length < 2) {
      resultatsBox.style.display = 'none';
      resultatsBox.innerHTML = '';
      return;
    }

    rechercheTimer = setTimeout(() => {
      fetch(`{{ route('visiteurs.rechercher') }}?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(data => {
          if (!data.length) {
            resultatsBox.innerHTML = '<div style="padding:0.75rem 1rem;font-size:0.85rem;color:var(--muted);">Aucun visiteur existant trouvé.</div>';
            resultatsBox.style.display = 'block';
            return;
          }
          const echapper = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
          resultatsBox.innerHTML = data.map((v, i) => `
            <div class="resultat-item" data-index="${i}" style="padding:0.65rem 1rem;cursor:pointer;border-bottom:1px solid var(--border);font-size:0.85rem;">
              <strong>${echapper(v.prenom)} ${echapper(v.nom)}</strong> — ${echapper(v.type)}${v.etablissement_nom ? ' · ' + echapper(v.etablissement_nom) : ''}
              <div style="font-size:0.72rem;color:var(--muted);">déjà venu(e) ${v.nb_visites}x, dernière visite le ${new Date(v.derniere_visite).toLocaleDateString('fr-FR')}</div>
            </div>
          `).join('');
          resultatsBox.querySelectorAll('.resultat-item').forEach(el => {
            el.addEventListener('mouseenter', () => el.style.background = 'var(--bg)');
            el.addEventListener('mouseleave', () => el.style.background = 'white');
            el.addEventListener('click', () => {
              const v = data[el.dataset.index];
              document.getElementById('fPrenom').value = v.prenom;
              document.getElementById('fNom').value = v.nom;
              document.getElementById('fSexe').value = v.sexe;
              document.getElementById('fType').value = v.type;
              document.getElementById('fEtab').value = v.etablissement_id || '';
              document.getElementById('fClasse').value = v.classe_ou_poste || '';
              document.getElementById('fTelephone').value = v.telephone || '';
              document.getElementById('fEmail').value = v.email || '';
              document.getElementById('fRecherche').value = `${v.prenom} ${v.nom}`;
              resultatsBox.style.display = 'none';
              showToast('Fiche pré-remplie — vérifiez la date de visite.', 'success');
            });
          });
          resultatsBox.style.display = 'block';
        });
    }, 300);
  });

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
