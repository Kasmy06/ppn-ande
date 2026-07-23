<?php

namespace Tests\Feature\Calendrier;

use App\Models\Etablissement;
use App\Models\ParametreApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationCapaciteTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_within_capacity_is_accepted(): void
    {
        ParametreApplication::set('capacite_journaliere', 60);
        $admin = User::factory()->superAdmin()->create();
        $etablissement = Etablissement::factory()->create();

        $response = $this->actingAs($admin)->post('/calendrier', [
            'etablissement_id' => $etablissement->id,
            'date' => '2026-09-10',
            'creneau' => 'matin',
            'nb_participants_prevu' => 30,
            'nb_encadrants_prevu' => 2,
            'statut' => 'confirmee',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reservations', ['date' => '2026-09-10 00:00:00', 'nb_participants_prevu' => 30]);
    }

    public function test_reservation_exceeding_capacity_is_rejected(): void
    {
        ParametreApplication::set('capacite_journaliere', 60);
        $admin = User::factory()->superAdmin()->create();
        $etablissement = Etablissement::factory()->create();

        $this->actingAs($admin)->post('/calendrier', [
            'etablissement_id' => $etablissement->id,
            'date' => '2026-09-11',
            'creneau' => 'matin',
            'nb_participants_prevu' => 40,
            'nb_encadrants_prevu' => 2,
            'statut' => 'confirmee',
        ]);

        $response = $this->actingAs($admin)->post('/calendrier', [
            'etablissement_id' => $etablissement->id,
            'date' => '2026-09-11',
            'creneau' => 'apres_midi',
            'nb_participants_prevu' => 30,
            'nb_encadrants_prevu' => 2,
            'statut' => 'confirmee',
        ]);

        $response->assertSessionHasErrors('nb_participants_prevu');
        $this->assertDatabaseMissing('reservations', ['date' => '2026-09-11 00:00:00', 'nb_participants_prevu' => 30]);
    }

    public function test_cancelled_reservations_do_not_count_towards_capacity(): void
    {
        ParametreApplication::set('capacite_journaliere', 60);
        $admin = User::factory()->superAdmin()->create();
        $etablissement = Etablissement::factory()->create();

        $this->actingAs($admin)->post('/calendrier', [
            'etablissement_id' => $etablissement->id,
            'date' => '2026-09-12',
            'creneau' => 'matin',
            'nb_participants_prevu' => 50,
            'nb_encadrants_prevu' => 2,
            'statut' => 'annulee',
        ]);

        $response = $this->actingAs($admin)->post('/calendrier', [
            'etablissement_id' => $etablissement->id,
            'date' => '2026-09-12',
            'creneau' => 'apres_midi',
            'nb_participants_prevu' => 40,
            'nb_encadrants_prevu' => 2,
            'statut' => 'confirmee',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reservations', ['date' => '2026-09-12 00:00:00', 'nb_participants_prevu' => 40]);
    }

    public function test_agent_cannot_create_reservation(): void
    {
        $agent = User::factory()->agent()->create();
        $etablissement = Etablissement::factory()->create();

        $response = $this->actingAs($agent)->post('/calendrier', [
            'etablissement_id' => $etablissement->id,
            'date' => '2026-09-13',
            'creneau' => 'matin',
            'nb_participants_prevu' => 10,
            'nb_encadrants_prevu' => 1,
            'statut' => 'confirmee',
        ]);

        $response->assertForbidden();
    }
}
