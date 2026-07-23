@extends('layouts.app')

@php
  $joursSemaine = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
  $statutLabels = ['confirmee' => 'Confirmée', 'en_attente' => 'En attente', 'annulee' => 'Annulée'];
  $statutColors = ['confirmee' => 'green', 'en_attente' => 'orange', 'annulee' => 'blue'];
  $moisPrecedent = $mois->copy()->subMonth();
  $moisSuivant = $mois->copy()->addMonth();
@endphp

@section('title', 'Calendrier')
@section('page-title', 'Calendrier des Réservations')
@section('breadcrumb', 'Accueil / Calendrier')

@section('topbar-actions')
  <div class="date-chip" style="display:flex;align-items:center;gap:0.75rem;">
    <a href="{{ route('calendrier.index', ['annee' => $moisPrecedent->year, 'mois' => $moisPrecedent->month]) }}" style="color:inherit;"><i class="fas fa-chevron-left"></i></a>
    <strong>{{ ucfirst($mois->translatedFormat('F Y')) }}</strong>
    <a href="{{ route('calendrier.index', ['annee' => $moisSuivant->year, 'mois' => $moisSuivant->month]) }}" style="color:inherit;"><i class="fas fa-chevron-right"></i></a>
  </div>
@endsection

@section('content')

  <div class="table-card" style="padding:1rem 1.25rem;margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
    <div style="display:flex;gap:1.5rem;flex-wrap:wrap;font-size:0.82rem;color:var(--muted);">
      <span><span class="badge badge-green">■</span> Confirmée</span>
      <span><span class="badge badge-orange">■</span> En attente</span>
      <span><span class="badge badge-blue">■</span> Annulée</span>
      <span><i class="fas fa-gauge-high"></i> Capacité journalière : <strong>{{ $capaciteJournaliere }}</strong> participants</span>
    </div>
    @if (auth()->user()->isSuperAdmin())
      <button type="button" class="btn btn-primary btn-sm" onclick="openCreateModal('{{ now()->format('Y-m-d') }}')"><i class="fas fa-plus"></i> Nouvelle réservation</button>
    @endif
  </div>

  <div class="table-card" style="overflow:hidden;">
    <div style="display:grid;grid-template-columns:repeat(7,1fr);border-bottom:1px solid var(--border);">
      @foreach ($joursSemaine as $j)
        <div style="padding:0.75rem;text-align:center;font-size:0.72rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--muted);background:var(--bg);">{{ $j }}</div>
      @endforeach
    </div>
    <div style="display:grid;grid-template-columns:repeat(7,1fr);">
      @foreach ($jours as $jour)
        @php
          $key = $jour->format('Y-m-d');
          $resasJour = $reservations->get($key, collect());
          $horsMois = $jour->month !== $mois->month;
          $participantsJour = $resasJour->where('statut', '!=', 'annulee')->sum('nb_participants_prevu');
        @endphp
        <div style="min-height:110px;border-right:1px solid var(--border);border-bottom:1px solid var(--border);padding:0.5rem;background:{{ $horsMois ? 'var(--bg)' : 'white' }};{{ $jour->isToday() ? 'box-shadow: inset 0 0 0 2px var(--blue-mid);' : '' }}">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.35rem;">
            <span style="font-size:0.78rem;font-weight:{{ $jour->isToday() ? '700' : '500' }};color:{{ $horsMois ? 'var(--muted)' : 'var(--text)' }};">{{ $jour->day }}</span>
            @if ($participantsJour > 0)
              <span style="font-size:0.65rem;color:var(--muted);">{{ $participantsJour }}/{{ $capaciteJournaliere }}</span>
            @endif
          </div>
          @foreach ($resasJour as $r)
            <div
              @if (auth()->user()->isSuperAdmin())
                onclick="openEditModal({
                  action: '{{ route('calendrier.update', $r->id) }}',
                  id: {{ $r->id }},
                  etablissement_id: {{ $r->etablissement_id }},
                  date: {{ Js::from($r->date->format('Y-m-d')) }},
                  creneau: {{ Js::from($r->creneau) }},
                  nb_participants_prevu: {{ $r->nb_participants_prevu }},
                  nb_encadrants_prevu: {{ $r->nb_encadrants_prevu }},
                  statut: {{ Js::from($r->statut) }}
                })"
                style="cursor:pointer;"
              @endif
              class="badge badge-{{ $statutColors[$r->statut] }}"
              style="display:block;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:500;"
              title="{{ $r->etablissement->nom }} — {{ $r->creneau === 'matin' ? 'Matin' : 'Après-midi' }} — {{ $r->nb_participants_prevu }} pers.">
              {{ $r->creneau === 'matin' ? '🌅' : '🌇' }} {{ Str::limit($r->etablissement->nom, 14) }}
            </div>
          @endforeach
        </div>
      @endforeach
    </div>
  </div>

  @if (auth()->user()->isSuperAdmin())
  <!-- MODAL -->
  <div class="modal-overlay" id="modalOverlay">
    <div class="modal">
      <form method="POST" action="{{ old('reservation_id') ? route('calendrier.update', old('reservation_id')) : route('calendrier.store') }}" id="resaForm">
        @csrf
        <input type="hidden" name="_method" id="methodField" value="{{ old('reservation_id') ? 'PUT' : 'POST' }}">
        <input type="hidden" name="reservation_id" id="fId" value="{{ old('reservation_id') }}">

        <div class="modal-header">
          <div class="modal-title" id="modalTitle">{{ old('reservation_id') ? 'Modifier la réservation' : 'Nouvelle réservation' }}</div>
          <button type="button" class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Établissement *</label>
            <select class="form-control" name="etablissement_id" id="fEtab" required>
              <option value="">Sélectionner</option>
              @foreach ($etablissements as $e)
                <option value="{{ $e->id }}" {{ (string) old('etablissement_id') === (string) $e->id ? 'selected' : '' }}>{{ $e->nom }}</option>
              @endforeach
            </select>
            @error('etablissement_id') <div class="field-error">{{ $message }}</div> @enderror
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Date *</label>
              <input class="form-control" type="date" name="date" id="fDate" value="{{ old('date') }}" required/>
              @error('date') <div class="field-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
              <label>Créneau *</label>
              <select class="form-control" name="creneau" id="fCreneau" required>
                <option value="matin" {{ old('creneau') === 'matin' ? 'selected' : '' }}>Matin</option>
                <option value="apres_midi" {{ old('creneau') === 'apres_midi' ? 'selected' : '' }}>Après-midi</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Participants prévus *</label>
              <input class="form-control" type="number" name="nb_participants_prevu" id="fParticipants" min="1" max="500" value="{{ old('nb_participants_prevu') }}" required/>
              @error('nb_participants_prevu') <div class="field-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
              <label>Encadrants prévus</label>
              <input class="form-control" type="number" name="nb_encadrants_prevu" id="fEncadrants" min="0" max="100" value="{{ old('nb_encadrants_prevu', 1) }}"/>
            </div>
          </div>
          <div class="form-group">
            <label>Statut *</label>
            <select class="form-control" name="statut" id="fStatut" required>
              <option value="en_attente" {{ old('statut', 'en_attente') === 'en_attente' ? 'selected' : '' }}>En attente</option>
              <option value="confirmee" {{ old('statut') === 'confirmee' ? 'selected' : '' }}>Confirmée</option>
              <option value="annulee" {{ old('statut') === 'annulee' ? 'selected' : '' }}>Annulée</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" id="btnDeleteResa" style="{{ old('reservation_id') ? '' : 'display:none;' }}margin-right:auto;" data-action="{{ old('reservation_id') ? route('calendrier.destroy', old('reservation_id')) : '' }}" onclick="deleteReservation()"><i class="fas fa-trash"></i> Supprimer</button>
          <button type="button" class="btn btn-outline" onclick="closeModal()">Annuler</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
        </div>
      </form>
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

  function openCreateModal(date) {
    document.getElementById('modalTitle').textContent = 'Nouvelle réservation';
    const form = document.getElementById('resaForm');
    form.action = "{{ route('calendrier.store') }}";
    document.getElementById('methodField').value = 'POST';
    document.getElementById('fId').value = '';
    form.reset();
    document.getElementById('fDate').value = date;
    document.getElementById('fEncadrants').value = 1;
    document.getElementById('btnDeleteResa').style.display = 'none';
    openModal();
  }

  function openEditModal(r) {
    document.getElementById('modalTitle').textContent = 'Modifier la réservation';
    const form = document.getElementById('resaForm');
    form.action = r.action;
    document.getElementById('methodField').value = 'PUT';
    document.getElementById('fId').value = r.id;
    document.getElementById('fEtab').value = r.etablissement_id;
    document.getElementById('fDate').value = r.date;
    document.getElementById('fCreneau').value = r.creneau;
    document.getElementById('fParticipants').value = r.nb_participants_prevu;
    document.getElementById('fEncadrants').value = r.nb_encadrants_prevu;
    document.getElementById('fStatut').value = r.statut;
    document.getElementById('btnDeleteResa').style.display = 'inline-flex';
    document.getElementById('btnDeleteResa').dataset.action = r.action;
    openModal();
  }

  function deleteReservation() {
    const url = document.getElementById('btnDeleteResa').dataset.action;
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteForm').submit();
  }

  @if ($errors->any())
    document.addEventListener('DOMContentLoaded', () => openModal());
  @endif
</script>
@endpush
