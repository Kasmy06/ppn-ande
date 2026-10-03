<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CompteurVisitesTest extends TestCase
{
    use RefreshDatabase;

    private function visitesAujourdhui(): int
    {
        return (int) DB::table('visites_journalieres')->where('jour', today()->toDateString())->value('nombre');
    }

    public function test_a_session_is_counted_once_per_day(): void
    {
        $this->get('/')->assertOk();
        $this->assertSame(1, $this->visitesAujourdhui());

        // Même session (même cookie) : pas de nouveau comptage le même jour.
        $this->get('/a-propos')->assertOk();
        $this->assertSame(1, $this->visitesAujourdhui());

        // Une autre session compte à nouveau.
        $this->flushSession();
        $this->get('/')->assertOk();
        $this->assertSame(2, $this->visitesAujourdhui());
    }

    public function test_only_successful_get_pages_are_counted(): void
    {
        $this->get('/page-inexistante')->assertNotFound();
        $this->assertSame(0, $this->visitesAujourdhui());

        $this->post('/contact', [])->assertSessionHasErrors();
        $this->assertSame(0, $this->visitesAujourdhui());
    }

    public function test_counter_is_displayed_and_can_be_hidden_by_the_team(): void
    {
        DB::table('visites_journalieres')->insert(['jour' => today()->toDateString(), 'nombre' => 42]);

        $this->get('/')->assertSee('42 visites depuis la mise en ligne', false);

        $this->actingAs(User::factory()->superAdmin()->create());
        $this->put('/parametres/application', ['nom_structure' => 'PPN', 'capacite_journaliere' => 60, 'visible_compteur' => 0]);

        $this->get('/')->assertDontSee('visites depuis la mise en ligne', false);
    }
}
