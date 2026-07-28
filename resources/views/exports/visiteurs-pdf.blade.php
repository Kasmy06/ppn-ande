<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
    h1 { font-size: 16px; color: #3B0B4D; margin-bottom: 2px; }
    .sub { color: #64748b; font-size: 10px; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #f1f5f9; text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; color: #475569; border-bottom: 1px solid #e2e8f0; }
    td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }
    .footer { margin-top: 16px; font-size: 9px; color: #94a3b8; }
  </style>
</head>
<body>
  <h1>PPN d'Andé — Liste des visiteurs</h1>
  <div class="sub">Exporté le {{ now()->format('d/m/Y à H:i') }} — {{ $visiteurs->count() }} visiteur(s)</div>

  <table>
    <thead>
      <tr>
        <th>Prénom</th>
        <th>Nom</th>
        <th>Genre</th>
        <th>Type</th>
        <th>Établissement</th>
        <th>Classe / Poste</th>
        <th>Téléphone</th>
        <th>Email</th>
        <th>Date</th>
        <th>Heure</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($visiteurs as $v)
        <tr>
          <td>{{ $v->prenom }}</td>
          <td>{{ $v->nom }}</td>
          <td>{{ $v->sexe === 'M' ? 'Masculin' : 'Féminin' }}</td>
          <td>{{ $v->type }}</td>
          <td>{{ $v->etablissement->nom ?? '' }}</td>
          <td>{{ $v->classe_ou_poste }}</td>
          <td>{{ $v->telephone }}</td>
          <td>{{ $v->email }}</td>
          <td>{{ $v->date_visite->format('d/m/Y') }}</td>
          <td>{{ $v->heureFormatee ?? '' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="footer">Point de Présence Numérique d'Andé — document généré automatiquement.</div>
</body>
</html>
