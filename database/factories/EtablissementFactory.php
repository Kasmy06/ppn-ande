<?php

namespace Database\Factories;

use App\Models\Etablissement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Etablissement>
 */
class EtablissementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => fake()->unique()->company(),
            'type' => fake()->randomElement(['scolaire', 'collectivite', 'association', 'autre']),
            'ville' => fake()->city(),
            'code_postal' => fake()->postcode(),
            'contact_nom' => fake()->name(),
            'contact_email' => fake()->safeEmail(),
        ];
    }
}
