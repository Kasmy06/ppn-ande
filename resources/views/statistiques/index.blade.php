@extends('layouts.app')

@section('title', 'Statistiques')
@section('page-title', 'Statistiques Avancées')
@section('breadcrumb', 'Accueil / Statistiques')

@section('content')

  <form method="GET" action="{{ route('statistiques.index') }}" class="filter-panel">
    <div class="filter-title"><i class="fas fa-sliders"></i> Filtres d'Analyse</div>
    <div class="filter-grid">
      <div class="filter-group">
        <label>Date début</label>
        <input type="date" name="date_debut" value="{{ $filters['date_debut'] ?? now()->startOfYear()->format('Y-m-d') }}"/>
      </div>
      <div class="filter-group">
        <label>Date fin</label>
        <input type="date" name="date_fin" value="{{ $filters['date_fin'] ?? now()->endOfYear()->format('Y-m-d') }}"/>
      </div>
      <div class="filter-group">
        <label>Type</label>
        <select name="type">
          <option value="">Tous</option>
          <option value="Élève" {{ ($filters['type'] ?? '') === 'Élève' ? 'selected' : '' }}>Élèves</option>
          <option value="Fonctionnaire" {{ ($filters['type'] ?? '') === 'Fonctionnaire' ? 'selected' : '' }}>Fonctionnaires</option>
          <option value="Externe" {{ ($filters['type'] ?? '') === 'Externe' ? 'selected' : '' }}>Externes</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Genre</label>
        <select name="sexe">
          <option value="">Tous</option>
          <option value="M" {{ ($filters['sexe'] ?? '') === 'M' ? 'selected' : '' }}>Masculin</option>
          <option value="F" {{ ($filters['sexe'] ?? '') === 'F' ? 'selected' : '' }}>Féminin</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Établissement</label>
        <select name="etablissement_id">
          <option value="">Tous</option>
          @foreach ($etablissementsListe as $e)
            <option value="{{ $e->id }}" {{ (string) ($filters['etablissement_id'] ?? '') === (string) $e->id ? 'selected' : '' }}>{{ $e->nom }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div style="margin-top:1rem;" class="filter-actions">
      <button type="submit" class="btn btn-primary"><i class="fas fa-magnifying-glass-chart"></i> Analyser</button>
      <button type="button" class="btn btn-outline" onclick="window.location.href='{{ route('statistiques.index') }}'"><i class="fas fa-rotate-left"></i> Réinitialiser</button>
    </div>
  </form>

  <div class="kpi-row">
    <div class="kpi-card">
      <div class="kpi-icon" style="background:#f6ecf8;color:#8E2E8E;"><i class="fas fa-users"></i></div>
      <div class="kpi-val">{{ $total }}</div>
      <div class="kpi-label">Total visiteurs</div>
      <div class="kpi-bar"><div class="progress-fill" style="width:100%;background:#8E2E8E;height:4px;"></div></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon" style="background:#d1fae5;color:#059669;"><i class="fas fa-graduation-cap"></i></div>
      <div class="kpi-val">{{ $eleves }}</div>
      <div class="kpi-label">Élèves</div>
      <div class="kpi-bar"><div class="progress-fill" style="width:{{ $total ? round($eleves/$total*100) : 0 }}%;background:#059669;height:4px;"></div></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-mars"></i></div>
      <div class="kpi-val">{{ $garcons }}</div>
      <div class="kpi-label">Garçons</div>
      <div class="kpi-bar"><div class="progress-fill" style="width:{{ $total ? round($garcons/$total*100) : 0 }}%;background:#d97706;height:4px;"></div></div>
    </div>
    <div class="kpi-card">
      <div class="kpi-icon" style="background:#fdf4ff;color:#7e22ce;"><i class="fas fa-venus"></i></div>
      <div class="kpi-val">{{ $filles }}</div>
      <div class="kpi-label">Filles</div>
      <div class="kpi-bar"><div class="progress-fill" style="width:{{ $total ? round($filles/$total*100) : 0 }}%;background:#7e22ce;height:4px;"></div></div>
    </div>
  </div>

  <div class="charts-grid-2">
    <div class="chart-card">
      <div class="chart-header">
        <div><div class="chart-title">Évolution Mensuelle</div><div class="chart-sub">Courbe de fréquentation sur la période</div></div>
      </div>
      <div class="chart-container"><canvas id="chartLine"></canvas></div>
    </div>
    <div class="chart-card">
      <div class="chart-header">
        <div><div class="chart-title">Répartition par Type</div><div class="chart-sub">Élèves / Fonctionnaires / Externes</div></div>
      </div>
      <div class="chart-container"><canvas id="chartType"></canvas></div>
    </div>
  </div>

  <div class="charts-grid-2">
    <div class="chart-card">
      <div class="chart-header">
        <div><div class="chart-title">Top Établissements</div><div class="chart-sub">Nombre de visiteurs sur la période</div></div>
      </div>
      <div class="chart-container"><canvas id="chartEtab"></canvas></div>
    </div>
    <div class="chart-card">
      <div class="chart-header">
        <div><div class="chart-title">Comparaison Genre</div><div class="chart-sub">Garçons vs Filles par mois</div></div>
      </div>
      <div class="chart-container"><canvas id="chartGenre"></canvas></div>
    </div>
  </div>

  <div class="tables-row">
    <div class="table-card">
      <div class="table-card-header">Résumé par Établissement</div>
      <table>
        <thead><tr><th>Établissement</th><th>Visiteurs</th><th>%</th><th>Répartition</th></tr></thead>
        <tbody>
          @forelse ($resumeEtablissements as $r)
            <tr>
              <td>{{ $r['nom'] }}</td>
              <td><strong>{{ $r['total'] }}</strong></td>
              <td>{{ $r['pourcentage'] }}%</td>
              <td><div class="progress-bar"><div class="progress-fill" style="width:{{ $r['pourcentage'] }}%;background:#8E2E8E;"></div></div></td>
            </tr>
          @empty
            <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:1.5rem;">Aucune donnée sur cette période.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="table-card">
      <div class="table-card-header">Fréquentation par Mois</div>
      <table>
        <thead><tr><th>Mois</th><th>Visiteurs</th><th>Variation</th></tr></thead>
        <tbody>
          @foreach ($frequentationMensuelle as $m)
            <tr>
              <td>{{ $m['mois'] }}</td>
              <td><strong>{{ $m['total'] }}</strong></td>
              <td>
                @if (is_null($m['variation']))
                  <span style="color:var(--muted);">—</span>
                @else
                  <span style="color:{{ $m['variation'] >= 0 ? '#059669' : '#dc2626' }};font-weight:600;">
                    {{ $m['variation'] >= 0 ? '+' : '' }}{{ $m['variation'] }}%
                  </span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="table-card" style="margin-bottom:1.75rem;">
    <div class="table-card-header">
      <div class="table-info">
        <div class="title">Visiteurs les plus fidèles</div>
        <div class="count">Personnes venues plus d'une fois sur la période sélectionnée</div>
      </div>
    </div>
    <div style="overflow-x:auto;">
      <table>
        <thead>
          <tr><th>#</th><th>Nom</th><th>Type</th><th>Établissement</th><th>Visites</th><th>Dernière visite</th></tr>
        </thead>
        <tbody>
          @forelse ($topVisiteurs as $i => $v)
            <tr>
              <td><strong>{{ $i + 1 }}</strong></td>
              <td>
                <div class="avatar-name">
                  <div class="mini-avatar" style="background:#f6ecf8;color:#8E2E8E;">{{ mb_strtoupper(mb_substr($v->prenom, 0, 1).mb_substr($v->nom, 0, 1)) }}</div>
                  <a href="{{ route('visiteurs.fiche', ['prenom' => $v->prenom, 'nom' => $v->nom, 'sexe' => $v->sexe]) }}" class="name-link">{{ $v->prenom }} {{ $v->nom }}</a>
                </div>
              </td>
              <td><span class="badge badge-{{ $v->type === 'Élève' ? 'blue' : ($v->type === 'Fonctionnaire' ? 'orange' : 'green') }}">{{ $v->type }}</span></td>
              <td>{{ $v->etablissement_nom ?? '—' }}</td>
              <td><span class="badge" style="background:#f6ecf8;color:#8E2E8E;">{{ $v->nb_visites }}x</span></td>
              <td>{{ \Illuminate\Support\Carbon::parse($v->derniere_visite)->format('d/m/Y') }}</td>
            </tr>
          @empty
            <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:1.5rem;">Aucun visiteur revenu plus d'une fois sur cette période.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @php
    $exportFilters = array_filter(['type' => $filters['type'] ?? null, 'sexe' => $filters['sexe'] ?? null]);
  @endphp
  <div class="export-section">
    <div class="export-text">
      <h3><i class="fas fa-file-export"></i> Exporter les données</h3>
      <p>Téléchargez la liste des visiteurs correspondant aux filtres Type / Genre ci-dessus</p>
    </div>
    <div class="export-btns">
      <a href="{{ route('visiteurs.export', $exportFilters + ['format' => 'xlsx']) }}" class="btn-export white">
        <i class="fas fa-file-excel"></i> Excel (.xlsx)
      </a>
      <a href="{{ route('visiteurs.export', $exportFilters + ['format' => 'pdf']) }}" class="btn-export white">
        <i class="fas fa-file-pdf"></i> PDF
      </a>
      <a href="{{ route('visiteurs.export', $exportFilters + ['format' => 'csv']) }}" class="btn-export outline-white">
        <i class="fas fa-file-csv"></i> CSV
      </a>
    </div>
  </div>

@endsection

@push('head')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
@endpush

@push('scripts')
<script>
  Chart.defaults.font.family = "'DM Sans', sans-serif";

  new Chart(document.getElementById('chartLine'), {
    type: 'line',
    data: {
      labels: @json($moisLabels),
      datasets: [{
        label: 'Visiteurs',
        data: @json($evolutionMensuelle),
        borderColor: '#8E2E8E', backgroundColor: 'rgba(142,46,142,0.1)',
        borderWidth: 2.5, pointBackgroundColor: '#8E2E8E', pointRadius: 5, fill: true, tension: 0.4,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' }, beginAtZero: true } }
    }
  });

  new Chart(document.getElementById('chartType'), {
    type: 'doughnut',
    data: {
      labels: ['Élèves', 'Fonctionnaires', 'Externes'],
      datasets: [{
        data: [{{ $repartitionType['Élève'] }}, {{ $repartitionType['Fonctionnaire'] }}, {{ $repartitionType['Externe'] }}],
        backgroundColor: ['#8E2E8E', '#1FA24B', '#F5B301'],
        borderWidth: 3, borderColor: '#fff', hoverOffset: 8,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '65%',
      plugins: { legend: { position: 'bottom', labels: { padding: 14, font: { size: 11 }, usePointStyle: true } } }
    }
  });

  new Chart(document.getElementById('chartEtab'), {
    type: 'bar',
    data: {
      labels: @json($topEtablissements->pluck('nom')),
      datasets: [{
        data: @json($topEtablissements->pluck('visiteurs_count')),
        backgroundColor: ['#8E2E8E', '#10b981', '#F5B301', '#8b5cf6', '#f59e0b'],
        borderRadius: 8, borderSkipped: false,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' }, beginAtZero: true } }
    }
  });

  new Chart(document.getElementById('chartGenre'), {
    type: 'bar',
    data: {
      labels: @json($moisLabels),
      datasets: [
        { label: 'Garçons', data: @json($genreParMois->pluck('garcons')), backgroundColor: '#8E2E8E', borderRadius: 6 },
        { label: 'Filles', data: @json($genreParMois->pluck('filles')), backgroundColor: '#1FA24B', borderRadius: 6 },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 11 } } } },
      scales: { x: { grid: { display: false } }, y: { grid: { color: '#f1f5f9' }, beginAtZero: true } }
    }
  });
</script>
@endpush
