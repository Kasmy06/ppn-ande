<?php

namespace Database\Factories;

use App\Models\Etablissement;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'etablissement_id' => Etablissement::factory(),
            'date' => fake()->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            'creneau' => fake()->randomElement(['matin', 'apres_midi']),
            'nb_participants_prevu' => fake()->numberBetween(5, 30),
            'nb_encadrants_prevu' => fake()->numberBetween(1, 4),
            'statut' => fake()->randomElement(['confirmee', 'en_attente', 'annulee']),
        ];
    }
}
