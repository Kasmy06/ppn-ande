<?php

namespace Database\Factories;

use App\Models\Etablissement;
use App\Models\Visiteur;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visiteur>
 */
class VisiteurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sexe = $this->faker->randomElement(['M', 'F']);
        $type = $this->faker->randomElement(['Élève', 'Élève', 'Élève', 'Élève', 'Fonctionnaire', 'Externe']);

        return [
            'prenom' => $sexe === 'M' ? $this->faker->firstNameMale() : $this->faker->firstNameFemale(),
            'nom' => $this->faker->lastName(),
            'sexe' => $sexe,
            'type' => $type,
            'etablissement_id' => Etablissement::inRandomOrder()->value('id'),
            'classe_ou_poste' => $type === 'Élève'
                ? $this->faker->randomElement(['CP', 'CE1', 'CE2', 'CM1', 'CM2', '6e', '5e', '4e', '3e', '2nde', '1ère', 'Terminale'])
                : ($type === 'Fonctionnaire' ? $this->faker->randomElement(['Inspecteur', 'Agent territorial', 'Enseignant', 'Directeur']) : null),
            'date_visite' => $this->faker->dateTimeBetween('-11 months', 'now')->format('Y-m-d'),
        ];
    }
}
