@extends('layouts.app')

@php
  $editingId = old('user_id');
  $editingUser = $editingId ? $users->firstWhere('id', (int) $editingId) : null;
  $modalAction = $editingId ? route('parametres.utilisateurs.update', $editingId) : route('parametres.utilisateurs.store');
  $modalMethod = $editingId ? 'PUT' : 'POST';
@endphp

@section('title', 'Paramètres')
@section('page-title', 'Paramètres')
@section('breadcrumb', 'Accueil / Paramètres / Utilisateurs')

@section('content')

  @include('parametres._tabs', ['active' => 'utilisateurs'])

  <div class="toolbar">
    <div style="flex:1;"></div>
    <button type="button" class="btn btn-primary" onclick="openCreateModal()"><i class="fas fa-user-plus"></i> Ajouter un utilisateur</button>
  </div>

  <div class="table-card">
    <div class="table-card-header">
      <div class="table-info"><div class="title">Comptes utilisateurs</div><div class="count">{{ $users->count() }} compte(s)</div></div>
    </div>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Actions</th></tr>
        </thead>
        <tbody>
          @foreach ($users as $u)
            <tr>
              <td>
                <div class="avatar-name">
                  <div class="mini-avatar" style="background:#dbeafe;color:#1d4ed8;">{{ $u->initiales() }}</div>
                  <strong>{{ $u->name }}</strong>
                  @if ($u->id === auth()->id())
                    <span style="color:var(--muted);font-size:0.72rem;">(vous)</span>
                  @endif
                </div>
              </td>
              <td>{{ $u->email }}</td>
              <td><span class="badge badge-{{ $u->role === 'super_admin' ? 'purple' : 'blue' }}">{{ $u->role === 'super_admin' ? 'Super Admin' : 'Agent' }}</span></td>
              <td>
                @if ($u->actif)
                  <span class="badge badge-green">Actif</span>
                @else
                  <span class="badge badge-orange">Désactivé</span>
                @endif
              </td>
              <td>
                <div class="action-btns">
                  <button type="button" class="btn btn-sm btn-outline" title="Modifier"
                    onclick="openEditModal({
                      action: '{{ route('parametres.utilisateurs.update', $u->id) }}',
                      id: {{ $u->id }},
                      name: {{ Js::from($u->name) }},
                      email: {{ Js::from($u->email) }},
                      role: {{ Js::from($u->role) }},
                      actif: {{ $u->actif ? 'true' : 'false' }}
                    })">
                    <i class="fas fa-pen"></i>
                  </button>
                  @if ($u->id !== auth()->id())
                    <button type="button" class="btn btn-sm btn-danger" title="Supprimer"
                      onclick="confirmDelete('{{ route('parametres.utilisateurs.destroy', $u->id) }}', {{ Js::from($u->name) }})">
                      <i class="fas fa-trash"></i>
                    </button>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- MODAL -->
  <div class="modal-overlay" id="modalOverlay">
    <div class="modal">
      <form method="POST" action="{{ $modalAction }}" id="userForm">
        @csrf
        <input type="hidden" name="_method" id="methodField" value="{{ $modalMethod }}">
        <input type="hidden" name="user_id" id="fId" value="{{ $editingId }}">

        <div class="modal-header">
          <div class="modal-title" id="modalTitle">{{ $editingId ? 'Modifier l\'utilisateur' : 'Ajouter un utilisateur' }}</div>
          <button type="button" class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nom complet *</label>
            <input class="form-control" type="text" name="name" id="fName" value="{{ old('name', $editingUser->name ?? '') }}" required/>
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input class="form-control" type="email" name="email" id="fEmail" value="{{ old('email', $editingUser->email ?? '') }}" required/>
            @error('email') <div class="field-error">{{ $message }}</div> @enderror
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Rôle *</label>
              <select class="form-control" name="role" id="fRole" required>
                <option value="agent" {{ old('role', $editingUser->role ?? '') === 'agent' ? 'selected' : '' }}>Agent d'accueil</option>
                <option value="super_admin" {{ old('role', $editingUser->role ?? '') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
              </select>
              @error('role') <div class="field-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group" id="actifWrap" style="display:none;">
              <label>Statut</label>
              <select class="form-control" name="actif" id="fActif">
                <option value="1">Actif</option>
                <option value="0">Désactivé</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label id="passwordLabel">Mot de passe *</label>
            <input class="form-control" type="password" name="password" id="fPassword"/>
            @error('password') <div class="field-error">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeModal()">Annuler</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

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

@endsection

@push('scripts')
<script>
  function openModal() { document.getElementById('modalOverlay').classList.add('open'); }
  function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); }

  function openCreateModal() {
    document.getElementById('modalTitle').textContent = 'Ajouter un utilisateur';
    const form = document.getElementById('userForm');
    form.action = "{{ route('parametres.utilisateurs.store') }}";
    document.getElementById('methodField').value = 'POST';
    document.getElementById('fId').value = '';
    form.reset();
    document.getElementById('actifWrap').style.display = 'none';
    document.getElementById('passwordLabel').textContent = 'Mot de passe *';
    document.getElementById('fPassword').required = true;
    openModal();
  }

  function openEditModal(u) {
    document.getElementById('modalTitle').textContent = 'Modifier l\'utilisateur';
    const form = document.getElementById('userForm');
    form.action = u.action;
    document.getElementById('methodField').value = 'PUT';
    document.getElementById('fId').value = u.id;
    document.getElementById('fName').value = u.name;
    document.getElementById('fEmail').value = u.email;
    document.getElementById('fRole').value = u.role;
    document.getElementById('fActif').value = u.actif ? '1' : '0';
    document.getElementById('actifWrap').style.display = 'block';
    document.getElementById('passwordLabel').textContent = 'Nouveau mot de passe (laisser vide pour ne pas changer)';
    document.getElementById('fPassword').required = false;
    openModal();
  }

  function confirmDelete(url, name) {
    document.getElementById('confirmText').textContent = `Le compte "${name}" sera définitivement supprimé.`;
    document.getElementById('deleteForm').action = url;
    document.getElementById('confirmOverlay').classList.add('open');
    document.getElementById('confirmDeleteBtn').onclick = () => document.getElementById('deleteForm').submit();
  }
  function closeConfirm() { document.getElementById('confirmOverlay').classList.remove('open'); }

  @if ($errors->any())
    document.addEventListener('DOMContentLoaded', () => {
      @if ($editingId)
        document.getElementById('actifWrap').style.display = 'block';
      @endif
      openModal();
    });
  @endif
</script>
@endpush
