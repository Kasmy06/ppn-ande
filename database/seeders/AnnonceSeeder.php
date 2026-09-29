<?php

namespace Database\Seeders;

use App\Models\Annonce;
use Illuminate\Database\Seeder;

class AnnonceSeeder extends Seeder
{
    public function run(): void
    {
        // [titre, contenu, urgente, décalage date de publication, décalage date d'expiration (ou null)]
        $annonces = [
            [
                'Fermeture exceptionnelle le 12 octobre',
                "Le PPN sera fermé toute la journée du 12 octobre pour une opération de maintenance de nos équipements informatiques.\nRéouverture normale dès le lendemain. Merci de votre compréhension.",
                true, '-1 day', '+15 days',
            ],
            [
                'Inscriptions ouvertes pour la formation bureautique',
                "Les inscriptions à la prochaine session de formation bureautique (traitement de texte et tableur) sont ouvertes.\nPlaces limitées à 12 participants : inscrivez-vous directement à l'accueil du PPN ou lors de votre prochaine visite.",
                false, '-3 days', '+20 days',
            ],
            [
                'Nouveaux horaires d\'ouverture',
                "À partir du mois prochain, le PPN sera également ouvert le samedi matin de 9h à 12h, en plus des horaires habituels en semaine.\nCette ouverture supplémentaire vise à faciliter l'accès pour les personnes en activité.",
                false, '-6 days', null,
            ],
            [
                'Panne du réseau internet résolue',
                "Suite à la coupure survenue la semaine dernière, la connexion internet du PPN est de nouveau pleinement opérationnelle.\nNous nous excusons pour la gêne occasionnée durant l'interruption.",
                false, '-12 days', '-2 days',
            ],
        ];

        foreach ($annonces as [$titre, $contenu, $urgente, $decalagePublication, $decalageExpiration]) {
            Annonce::firstOrCreate(['titre' => $titre], [
                'contenu' => $contenu,
                'urgente' => $urgente,
                'date_publication' => now()->modify($decalagePublication)->toDateString(),
                'date_expiration' => $decalageExpiration ? now()->modify($decalageExpiration)->toDateString() : null,
                'publie' => true,
            ]);
        }
    }
}
