@extends('layouts.app')

@section('title', 'Tableau de Bord')
@section('page-title', 'Tableau de Bord')
@section('breadcrumb', 'Accueil / Dashboard')

@section('topbar-actions')
  <div class="date-chip"><i class="far fa-calendar"></i> &nbsp;{{ ucfirst(now()->translatedFormat('d F Y')) }}</div>
@endsection

@section('content')

  <div class="stats-grid">
    <div class="stat-card blue">
      <div class="stat-top">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-trend {{ $trendVisiteurs >= 0 ? 'up' : 'down' }}">
          <i class="fas fa-arrow-trend-{{ $trendVisiteurs >= 0 ? 'up' : 'down' }}"></i> {{ $trendVisiteurs >= 0 ? '+' : '' }}{{ $trendVisiteurs }}%
        </div>
      </div>
      <div class="stat-num">{{ $totalVisiteurs }}</div>
      <div class="stat-label">Total Visiteurs</div>
    </div>

    <div class="stat-card green">
      <div class="stat-top">
        <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="stat-trend {{ $trendEleves >= 0 ? 'up' : 'down' }}">
          <i class="fas fa-arrow-trend-{{ $trendEleves >= 0 ? 'up' : 'down' }}"></i> {{ $trendEleves >= 0 ? '+' : '' }}{{ $trendEleves }}%
        </div>
      </div>
      <div class="stat-num">{{ $totalEleves }}</div>
      <div class="stat-label">Total Élèves</div>
    </div>

    <div class="stat-card orange">
      <div class="stat-top">
        <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
        <div class="stat-trend {{ $trendFonctionnaires >= 0 ? 'up' : 'down' }}">
          <i class="fas fa-arrow-trend-{{ $trendFonctionnaires >= 0 ? 'up' : 'down' }}"></i> {{ $trendFonctionnaires >= 0 ? '+' : '' }}{{ $trendFonctionnaires }}%
        </div>
      </div>
      <div class="stat-num">{{ $totalFonctionnaires }}</div>
      <div class="stat-label">Fonctionnaires</div>
    </div>

    <div class="stat-card purple">
      <div class="stat-top">
        <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
        <div class="stat-trend {{ $trendExternes >= 0 ? 'up' : 'down' }}">
          <i class="fas fa-arrow-trend-{{ $trendExternes >= 0 ? 'up' : 'down' }}"></i> {{ $trendExternes >= 0 ? '+' : '' }}{{ $trendExternes }}%
        </div>
      </div>
      <div class="stat-num">{{ $totalExternes }}</div>
      <div class="stat-label">Visiteurs Externes</div>
    </div>
  </div>

  <div class="charts-grid">
    <div class="chart-card">
      <div class="chart-header">
        <div>
          <div class="chart-title">Répartition par Genre</div>
          <div class="chart-sub">Garçons vs Filles</div>
        </div>
        <span class="chart-badge">{{ now()->year }}</span>
      </div>
      <div class="chart-container">
        <canvas id="chartGenre"></canvas>
      </div>
    </div>

    <div class="chart-card big-chart">
      <div class="chart-header">
        <div>
          <div class="chart-title">Fréquentation par Établissement</div>
          <div class="chart-sub">Top établissements partenaires</div>
        </div>
        <span class="chart-badge">Total</span>
      </div>
      <div class="chart-container">
        <canvas id="chartEtab"></canvas>
      </div>
    </div>
  </div>

  <div class="chart-card" style="margin-bottom:1.75rem;">
    <div class="chart-header">
      <div>
        <div class="chart-title">Courbe de Fréquentation Mensuelle</div>
        <div class="chart-sub">Évolution sur l'année {{ now()->year }}</div>
      </div>
      <span class="chart-badge">Année {{ now()->year }}</span>
    </div>
    <div class="chart-container" style="height:200px;">
      <canvas id="chartMensuel"></canvas>
    </div>
  </div>

  <div class="bottom-grid">
    <div class="table-card">
      <div class="table-card-header">
        <div class="table-info">
          <div class="title">Derniers Visiteurs</div>
        </div>
        <a href="{{ route('visiteurs.index') }}"><button class="btn btn-outline btn-sm">Voir tout</button></a>
      </div>
      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Type</th>
            <th>Établissement</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($derniersVisiteurs as $v)
            <tr>
              <td><a href="{{ route('visiteurs.fiche', ['prenom' => $v->prenom, 'nom' => $v->nom, 'sexe' => $v->sexe]) }}" class="name-link">{{ $v->prenom }} {{ $v->nom }}</a></td>
              <td><span class="badge badge-{{ $v->type === 'Élève' ? 'blue' : ($v->type === 'Fonctionnaire' ? 'orange' : 'green') }}">{{ $v->type }}</span></td>
              <td>{{ $v->etablissement->nom ?? '—' }}</td>
              <td>
                {{ $v->date_visite->format('d/m/Y') }}
                @if ($v->heureFormatee)
                  <div style="font-size:0.72rem;color:var(--muted);"><i class="far fa-clock"></i> {{ $v->heureFormatee }}</div>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:1.5rem;">Aucun visiteur enregistré pour le moment.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="activity-card">
      <div class="activity-header">Activité Récente</div>
      <div class="activity-list">
        @forelse ($activiteRecente as $a)
          <div class="activity-item">
            <div class="act-icon" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-user-plus"></i></div>
            <div class="act-text">
              <div class="act-main">Nouveau visiteur ajouté : <strong>{{ $a->prenom }} {{ $a->nom }}</strong>@if($a->auteur) par {{ $a->auteur->name }}@endif</div>
              <div class="act-time">{{ $a->created_at->diffForHumans() }}</div>
            </div>
          </div>
        @empty
          <div class="activity-item">
            <div class="act-text"><div class="act-main">Aucune activité récente.</div></div>
          </div>
        @endforelse
      </div>
    </div>
  </div>

@endsection

@push('head')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
@endpush

@push('scripts')
<script>
  Chart.defaults.font.family = "'DM Sans', sans-serif";

  new Chart(document.getElementById('chartGenre'), {
    type: 'doughnut',
    data: {
      labels: ['Garçons', 'Filles'],
      datasets: [{
        data: [{{ $garcons }}, {{ $filles }}],
        backgroundColor: ['#8E2E8E', '#1FA24B'],
        borderWidth: 3, borderColor: '#fff', hoverOffset: 8,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      cutout: '65%',
      plugins: { legend: { position: 'bottom', labels: { padding: 14, font: { size: 11 }, usePointStyle: true } } }
    }
  });

  new Chart(document.getElementById('chartEtab'), {
    type: 'bar',
    data: {
      labels: @json($topEtablissements->pluck('nom')),
      datasets: [{
        data: @json($topEtablissements->pluck('visiteurs_count')),
        backgroundColor: ['#8E2E8E', '#10b981', '#F5B301', '#8b5cf6', '#f59e0b', '#ef4444'],
        borderRadius: 8, borderSkipped: false,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' }, beginAtZero: true } }
    }
  });

  new Chart(document.getElementById('chartMensuel'), {
    type: 'line',
    data: {
      labels: @json($moisLabels),
      datasets: [{
        label: 'Visiteurs',
        data: @json($mensuel),
        borderColor: '#8E2E8E',
        backgroundColor: 'rgba(142,46,142,0.1)',
        borderWidth: 2.5,
        pointBackgroundColor: '#8E2E8E',
        pointRadius: 4,
        fill: true,
        tension: 0.4,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' }, beginAtZero: true } }
    }
  });
</script>
@endpush
