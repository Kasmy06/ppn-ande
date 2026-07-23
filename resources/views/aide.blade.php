@extends('layouts.app')

@section('title', 'Aide')
@section('page-title', 'Aide & Documentation')
@section('breadcrumb', 'Accueil / Aide')

@section('content')

  @php
    $sections = [
      ['icon' => 'chart-pie', 'title' => 'Tableau de Bord', 'text' => "Vue d'ensemble de la fréquentation : totaux par type de visiteur, répartition par genre, top établissements et courbe d'évolution mensuelle. Toutes les données affichées sont calculées en temps réel."],
      ['icon' => 'users', 'title' => 'Visiteurs', 'text' => "Liste complète des visiteurs enregistrés. Utilisez la recherche et les filtres (type, genre, date) pour retrouver une fiche. Le bouton « Ajouter visiteur » ouvre un formulaire ; cliquez sur le crayon pour modifier une fiche existante. La suppression est réservée aux comptes Super Admin."],
      ['icon' => 'school', 'title' => 'Établissements', 'text' => "Annuaire des établissements partenaires (écoles, collectivités, associations). La création, la modification et la suppression sont réservées aux comptes Super Admin ; les agents peuvent consulter la liste."],
      ['icon' => 'chart-bar', 'title' => 'Statistiques', 'text' => "Analyse avancée avec filtres par période, type, genre et établissement. Exportez les données filtrées en Excel, PDF ou CSV directement depuis cette page."],
      ['icon' => 'calendar-days', 'title' => 'Calendrier', 'text' => "Planification des visites : chaque réservation indique l'établissement, le créneau (matin/après-midi), le nombre de participants attendus et le statut. Le système refuse automatiquement une réservation qui dépasserait la capacité d'accueil journalière définie dans les Paramètres."],
      ['icon' => 'file-export', 'title' => 'Exports', 'text' => "Centre d'export centralisé : choisissez une période, un type de visiteur et un format (CSV, Excel, PDF), puis téléchargez. L'historique des exports générés est conservé pour traçabilité."],
      ['icon' => 'sliders', 'title' => 'Paramètres (Super Admin)', 'text' => "Gestion des comptes utilisateurs (créer, modifier le rôle, désactiver), configuration de l'application (nom de la structure, capacité d'accueil journalière) et consultation du journal d'activité de la plateforme."],
    ];
  @endphp

  <div class="table-card" style="padding:1.5rem;margin-bottom:1.5rem;">
    <p style="color:var(--muted);font-size:0.9rem;line-height:1.6;">
      Cette page résume le fonctionnement de chaque module de la plateforme du Point de Présence Numérique d'Andé.
      Pour toute question non couverte ici, contactez l'administrateur de la plateforme.
    </p>
  </div>

  <div class="charts-grid-2">
    @foreach ($sections as $s)
      <div class="chart-card">
        <div class="chart-header">
          <div style="display:flex;align-items:center;gap:0.75rem;">
            <div class="stat-icon" style="background:var(--blue-pale);color:var(--blue-mid);"><i class="fas fa-{{ $s['icon'] }}"></i></div>
            <div class="chart-title">{{ $s['title'] }}</div>
          </div>
        </div>
        <p style="font-size:0.85rem;color:var(--muted);line-height:1.6;">{{ $s['text'] }}</p>
      </div>
    @endforeach
  </div>

@endsection
