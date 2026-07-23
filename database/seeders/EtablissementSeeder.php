<?php

namespace Database\Seeders;

use App\Models\Etablissement;
use Illuminate\Database\Seeder;

class EtablissementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $etablissements = [
            ['nom' => 'École Jean Moulin', 'type' => 'scolaire', 'ville' => 'Andé', 'code_postal' => '27430', 'contact_nom' => 'Sylvie Renard', 'contact_email' => 'contact@ecole-jeanmoulin.fr'],
            ['nom' => 'Collège des Arts', 'type' => 'scolaire', 'ville' => 'Louviers', 'code_postal' => '27400', 'contact_nom' => 'Marc Dubois', 'contact_email' => 'contact@college-arts.fr'],
            ['nom' => 'Lycée Victor Hugo', 'type' => 'scolaire', 'ville' => 'Val-de-Reuil', 'code_postal' => '27100', 'contact_nom' => 'Isabelle Petit', 'contact_email' => 'contact@lycee-hugo.fr'],
            ['nom' => 'École Pasteur', 'type' => 'scolaire', 'ville' => 'Andé', 'code_postal' => '27430', 'contact_nom' => 'Julien Faure', 'contact_email' => 'contact@ecole-pasteur.fr'],
            ['nom' => 'Groupe Scolaire Andé', 'type' => 'scolaire', 'ville' => 'Andé', 'code_postal' => '27430', 'contact_nom' => 'Nathalie Girard', 'contact_email' => 'contact@gs-ande.fr'],
            ['nom' => "Mairie d'Andé", 'type' => 'collectivite', 'ville' => 'Andé', 'code_postal' => '27430', 'contact_nom' => 'Philippe Morel', 'contact_email' => 'contact@mairie-ande.fr'],
            ['nom' => 'Association Nature', 'type' => 'association', 'ville' => 'Andé', 'code_postal' => '27430', 'contact_nom' => 'Claire Bonnet', 'contact_email' => 'contact@asso-nature.fr'],
            ['nom' => 'Conseil Régional', 'type' => 'collectivite', 'ville' => 'Rouen', 'code_postal' => '76000', 'contact_nom' => 'Thomas Lambert', 'contact_email' => 'contact@region-normandie.fr'],
        ];

        foreach ($etablissements as $etablissement) {
            Etablissement::firstOrCreate(['nom' => $etablissement['nom']], $etablissement);
        }
    }
}
