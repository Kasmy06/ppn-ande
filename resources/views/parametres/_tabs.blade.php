<div class="toolbar" style="margin-bottom:1.25rem;">
  <a href="{{ route('parametres.utilisateurs.index') }}" class="btn {{ $active === 'utilisateurs' ? 'btn-primary' : 'btn-outline' }} btn-sm">
    <i class="fas fa-users-gear"></i> Utilisateurs
  </a>
  <a href="{{ route('parametres.application.edit') }}" class="btn {{ $active === 'application' ? 'btn-primary' : 'btn-outline' }} btn-sm">
    <i class="fas fa-sliders"></i> Application
  </a>
  <a href="{{ route('parametres.journal.index') }}" class="btn {{ $active === 'journal' ? 'btn-primary' : 'btn-outline' }} btn-sm">
    <i class="fas fa-clock-rotate-left"></i> Journal d'activité
  </a>
</div>
