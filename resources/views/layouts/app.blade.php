<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>PPN d'Andé – @yield('title', 'Tableau de Bord')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>
  @include('layouts._favicon')
  @stack('head')
</head>
<body>

<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    @php
      $logoPath = \App\Models\ParametreApplication::get('logo_path');
      $nomStructureSidebar = \App\Models\ParametreApplication::get('nom_structure', "PPN d'Andé");
    @endphp
    <div class="sidebar-logo">
      <div class="logo-icon" style="{{ $logoPath ? 'background:white;overflow:hidden;' : '' }}">
        @if ($logoPath)
          <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath) }}" alt="Logo" style="width:100%;height:100%;object-fit:contain;"/>
        @else
          <i class="fas fa-network-wired"></i>
        @endif
      </div>
      <div class="logo-text">
        <div class="name">{{ $nomStructureSidebar }}</div>
        <div class="sub">Point Numérique</div>
      </div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Principal</div>
    <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
      <i class="fas fa-chart-pie"></i> Tableau de Bord
    </a>
    <a class="nav-item {{ request()->routeIs('visiteurs.*') ? 'active' : '' }}" href="{{ route('visiteurs.index') }}">
      <i class="fas fa-users"></i> Visiteurs
      <span class="nav-badge">{{ \App\Models\Visiteur::count() }}</span>
    </a>
    <a class="nav-item {{ request()->routeIs('statistiques.*') ? 'active' : '' }}" href="{{ route('statistiques.index') }}">
      <i class="fas fa-chart-bar"></i> Statistiques
    </a>

    <div class="nav-section-label">Gestion</div>
    <a class="nav-item {{ request()->routeIs('etablissements.*') ? 'active' : '' }}" href="{{ route('etablissements.index') }}">
      <i class="fas fa-school"></i> Établissements
    </a>
    @if (auth()->user()->isSuperAdmin())
      <a class="nav-item {{ request()->routeIs('activites.*') ? 'active' : '' }}" href="{{ route('activites.index') }}">
        <i class="fas fa-bullhorn"></i> Activités (site)
      </a>
    @endif
    <a class="nav-item {{ request()->routeIs('medias.*') ? 'active' : '' }}" href="{{ route('medias.index') }}">
      <i class="fas fa-photo-film"></i> Photos & vidéos
    </a>
    <a class="nav-item {{ request()->routeIs('messages.*') ? 'active' : '' }}" href="{{ route('messages.index') }}">
      <i class="fas fa-inbox"></i> Messages
      @if (($nonLusMessages = \App\Models\MessageContact::where('lu', false)->count()) > 0)
        <span class="nav-badge">{{ $nonLusMessages }}</span>
      @endif
    </a>
    <a class="nav-item {{ request()->routeIs('calendrier.*') ? 'active' : '' }}" href="{{ route('calendrier.index') }}">
      <i class="fas fa-calendar-days"></i> Calendrier
    </a>
    <a class="nav-item {{ request()->routeIs('exports.*') ? 'active' : '' }}" href="{{ route('exports.index') }}">
      <i class="fas fa-file-export"></i> Exports
    </a>

    <div class="nav-section-label">Système</div>
    @if (auth()->user()->isSuperAdmin())
      <a class="nav-item {{ request()->routeIs('parametres.*') ? 'active' : '' }}" href="{{ route('parametres.utilisateurs.index') }}">
        <i class="fas fa-sliders"></i> Paramètres
      </a>
    @endif
    <a class="nav-item {{ request()->routeIs('profil.*') ? 'active' : '' }}" href="{{ route('profil.edit') }}">
      <i class="fas fa-user-gear"></i> Mon profil
    </a>
    <a class="nav-item {{ request()->routeIs('aide') ? 'active' : '' }}" href="{{ route('aide') }}">
      <i class="fas fa-circle-question"></i> Aide
    </a>
  </nav>
  <div class="sidebar-footer">
    <div class="user-card">
      <a href="{{ route('profil.edit') }}" style="text-decoration:none;display:flex;align-items:center;gap:0.75rem;flex:1;min-width:0;">
        <div class="avatar">{{ auth()->user()->initiales() }}</div>
        <div class="user-info">
          <div class="uname">{{ auth()->user()->name }}</div>
          <div class="urole">{{ auth()->user()->role === 'super_admin' ? 'Super Admin' : 'Agent d\'accueil' }}</div>
        </div>
      </a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-btn" title="Déconnexion"><i class="fas fa-right-from-bracket"></i></button>
      </form>
    </div>
  </div>
</aside>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <button class="hamburger" id="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
      <div>
        <div class="page-title">@yield('page-title', 'Tableau de Bord')</div>
        <div class="breadcrumb">@yield('breadcrumb', 'Accueil')</div>
      </div>
    </div>
    <div class="topbar-right">
      @yield('topbar-actions')
      <div class="icon-btn" title="Notifications">
        <i class="fas fa-bell"></i>
        <span class="notif-dot"></span>
      </div>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="icon-btn" title="Déconnexion"><i class="fas fa-right-from-bracket"></i></button>
      </form>
    </div>
  </header>

  <div class="page-content">
    @yield('content')
  </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
  }
  function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('overlay').classList.remove('show');
  }

  function showToast(msg, type = 'success') {
    const c = document.getElementById('toastContainer');
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    const icon = type === 'success' ? 'circle-check' : type === 'error' ? 'circle-xmark' : 'circle-info';
    t.innerHTML = `<i class="fas fa-${icon}"></i> ${msg}`;
    c.appendChild(t);
    setTimeout(() => {
      t.style.animation = 'slideOut 0.3s ease forwards';
      setTimeout(() => t.remove(), 300);
    }, 3500);
  }

  @if (session('success'))
    document.addEventListener('DOMContentLoaded', () => showToast(@json(session('success')), 'success'));
  @endif
  @if (session('error') || $errors->any())
    document.addEventListener('DOMContentLoaded', () => showToast(@json(session('error') ?? $errors->first()), 'error'));
  @endif
</script>
@stack('scripts')
</body>
</html>
