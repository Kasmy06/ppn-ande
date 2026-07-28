<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VisiteursExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Collection $visiteurs) {}

    public function collection(): Collection
    {
        return $this->visiteurs;
    }

    public function headings(): array
    {
        return ['Prénom', 'Nom', 'Genre', 'Type', 'Établissement', 'Classe / Poste', 'Téléphone', 'Email', 'Date de visite', 'Heure d\'arrivée'];
    }

    public function map($visiteur): array
    {
        return [
            $visiteur->prenom,
            $visiteur->nom,
            $visiteur->sexe === 'M' ? 'Masculin' : 'Féminin',
            $visiteur->type,
            $visiteur->etablissement->nom ?? '',
            $visiteur->classe_ou_poste,
            $visiteur->telephone,
            $visiteur->email,
            $visiteur->date_visite->format('d/m/Y'),
            $visiteur->heureFormatee ?? '',
        ];
    }
}
