<?php

namespace Tests\Feature;

use App\Models\Activite;
use App\Models\Media;
use App\Models\MessageContact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteCompletTest extends TestCase
{
    use RefreshDatabase;

    private function activite(array $extra = []): Activite
    {
        return Activite::create($extra + ['titre' => 'Atelier', 'categorie' => 'atelier', 'description' => 'Description', 'date_debut' => today()->addDay(), 'publie' => true]);
    }

    public function test_public_pages_render(): void
    {
        foreach (['/', '/a-propos', '/contact', '/agenda', '/agenda?mois=2026-03', '/agenda?mois=n-importe-quoi', '/galerie', '/activites'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_agenda_shows_activity_in_its_month(): void
    {
        $this->activite(['titre' => 'Formation agenda', 'date_debut' => '2026-03-10', 'date_fin' => '2026-03-12']);

        $this->get('/agenda?mois=2026-03')->assertSee('Formation agenda');
        $this->get('/agenda?mois=2026-05')->assertDontSee('Formation agenda');
    }

    public function test_search_filters_activities(): void
    {
        $this->activite(['titre' => 'Cybersécurité pour tous']);
        $this->activite(['titre' => 'Bureautique']);

        $this->get('/activites?q=cyber')->assertSee('Cybersécurité pour tous')->assertDontSee('Bureautique');
    }

    public function test_contact_form_stores_message_and_blocks_bots(): void
    {
        $donnees = ['nom' => 'Awa', 'email' => 'awa@example.ci', 'sujet' => 'Inscription', 'message' => "Bonjour, je souhaite m'inscrire.", 'consentement' => 1];

        $this->post('/contact', array_diff_key($donnees, ['consentement' => 1]))->assertSessionHasErrors('consentement');
        $this->post('/contact', $donnees)->assertRedirect('/contact');
        $this->assertDatabaseHas('messages_contact', ['nom' => 'Awa', 'lu' => false]);

        $this->post('/contact', $donnees + ['website' => 'http://spam'])->assertSessionHasErrors('website');
        $this->post('/contact', ['nom' => 'X'])->assertSessionHasErrors(['email', 'sujet', 'message']);
        $this->assertSame(1, MessageContact::count());
    }

    public function test_messages_are_visible_to_agents_but_only_admins_delete(): void
    {
        $message = MessageContact::create(['nom' => 'Awa', 'email' => 'a@b.ci', 'sujet' => 'Sujet visible', 'message' => 'Un message assez long.']);

        $this->actingAs(User::factory()->agent()->create());
        $this->get('/messages')->assertOk()->assertSee('Sujet visible');
        $this->patch("/messages/{$message->id}/lu")->assertRedirect();
        $this->assertTrue($message->fresh()->lu);
        $this->delete("/messages/{$message->id}")->assertForbidden();

        $this->actingAs(User::factory()->superAdmin()->create())->delete("/messages/{$message->id}")->assertRedirect();
        $this->assertSame(0, MessageContact::count());
    }

    public function test_contact_settings_are_displayed_and_map_is_restricted(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $base = ['nom_structure' => "PPN d'Andé", 'capacite_journaliere' => 60];
        $coordonnees = [
            'adresse' => 'Rue des Tests, Andé', 'telephones' => "+225 01 02 03 04 05\n+225 06 07 08 09 10",
            'email' => 'contact@ppn.ci', 'horaires' => 'Lun-Ven 8h-17h',
        ];

        $this->put('/parametres/application', $base + $coordonnees + ['carte_url' => 'https://evil.example/x'])
            ->assertSessionHasErrors('carte_url');

        $this->put('/parametres/application', $base + $coordonnees + ['carte_url' => 'https://www.google.com/maps/embed?pb=abc'])
            ->assertSessionHasNoErrors();

        $this->get('/parametres/application')->assertOk()->assertSee('Rue des Tests, Andé')->assertSee('+225 06 07 08 09 10');
        $this->get('/gestion-activites/create')->assertOk()->assertSee('Photos supplémentaires');

        $this->get('/contact')->assertSee('Rue des Tests, Andé')->assertSee('tel:+2250102030405', false)
            ->assertSee('contact@ppn.ci')->assertSee('Lun-Ven 8h-17h')->assertSee('https://www.google.com/maps/embed?pb=abc', false);
    }

    public function test_replacing_cover_replaces_gallery_copy_and_deleting_activity_cleans_up(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->superAdmin()->create());
        $base = ['titre' => 'Noël', 'categorie' => 'evenement', 'description' => 'd', 'date_debut' => '2026-12-24', 'publie' => 1];

        $this->post('/gestion-activites', $base + ['image' => UploadedFile::fake()->image('a.jpg')]);
        $a = Activite::first();
        $this->put("/gestion-activites/{$a->id}", $base + ['image' => UploadedFile::fake()->image('b.jpg')]);

        $this->assertSame(1, Media::where('activite_id', $a->id)->where('auto', true)->count());

        $this->put("/gestion-activites/{$a->id}", $base + ['photos' => [UploadedFile::fake()->image('c.jpg'), UploadedFile::fake()->image('d.jpg')]]);
        $this->assertSame(3, Media::where('activite_id', $a->id)->count());

        $copie = Media::where('auto', true)->first()->fichier_path;
        $this->delete("/gestion-activites/{$a->id}");
        Storage::disk('public')->assertMissing($copie);
        $this->assertSame(2, Media::count()); // les photos ajoutées à la main restent dans la galerie
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post('/login', ['email' => 'x@y.ci', 'password' => 'mauvais']);
        }
        $this->post('/login', ['email' => 'x@y.ci', 'password' => 'mauvais'])->assertStatus(429);
    }
}
