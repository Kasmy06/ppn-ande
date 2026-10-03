<?php

namespace Tests\Feature;

use App\Mail\NotificationEquipe;
use App\Models\Annonce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnnonceTest extends TestCase
{
    use RefreshDatabase;

    private array $base = ['titre' => 'Fermeture exceptionnelle', 'contenu' => 'Le PPN sera fermé le 12 octobre.', 'date_publication' => '2026-01-01'];

    public function test_new_announcement_is_a_draft_until_published(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->get('/gestion-annonces/create')->assertOk();
        $this->post('/gestion-annonces', $this->base);

        $annonce = Annonce::firstOrFail();
        $this->assertFalse($annonce->publie);
        $this->get('/annonces')->assertDontSee('Fermeture exceptionnelle');
        $this->get('/')->assertDontSee('Fermeture exceptionnelle');

        $this->patch("/gestion-annonces/{$annonce->id}/publication")->assertRedirect();
        $this->assertTrue($annonce->fresh()->publie);
        $this->get('/annonces')->assertSee('Fermeture exceptionnelle');
        $this->get('/')->assertSee('Fermeture exceptionnelle');

        $this->patch("/gestion-annonces/{$annonce->id}/publication");
        $this->get('/annonces')->assertDontSee('Fermeture exceptionnelle');
    }

    public function test_agent_can_add_announcement_but_only_as_draft_and_cannot_edit_or_delete(): void
    {
        Mail::fake();
        User::factory()->superAdmin()->create(['email' => 'admin@ppn.ci']);
        $this->actingAs(User::factory()->agent()->create());

        $this->get('/gestion-annonces')->assertOk();
        $this->get('/gestion-annonces/create')->assertOk()->assertSee('enregistrée en brouillon');
        $this->post('/gestion-annonces', $this->base + ['publie' => 1])->assertRedirect('/gestion-annonces');

        $annonce = Annonce::firstOrFail();
        $this->assertFalse($annonce->publie, 'un agent ne peut pas publier');
        $this->assertTrue($annonce->a_valider);
        Mail::assertSent(NotificationEquipe::class, fn ($m) => $m->hasTo('admin@ppn.ci') && str_contains($m->texte, 'Fermeture exceptionnelle'));

        $this->get('/gestion-annonces')->assertSee('Fermeture exceptionnelle')->assertSee('En attente de validation');
        $this->get("/gestion-annonces/{$annonce->id}/edit")->assertForbidden();
        $this->put("/gestion-annonces/{$annonce->id}", $this->base + ['titre' => 'Piraté'])->assertForbidden();
        $this->delete("/gestion-annonces/{$annonce->id}")->assertForbidden();

        $this->actingAs(User::factory()->superAdmin()->create())->patch("/gestion-annonces/{$annonce->id}/publication");
        $this->get('/annonces')->assertSee('Fermeture exceptionnelle');
    }

    public function test_expired_announcement_is_hidden_and_urgent_ones_come_first(): void
    {
        Annonce::create(['titre' => 'Passée', 'publie' => true, 'date_expiration' => '2026-01-05'] + $this->base);
        Annonce::create(['titre' => 'Normale', 'publie' => true] + $this->base);
        Annonce::create(['titre' => 'Urgente', 'publie' => true, 'urgente' => true] + $this->base);

        $this->travelTo('2026-06-01');

        $reponse = $this->get('/annonces')->assertOk()->assertDontSee('Passée')->assertSee('Urgente')->assertSee('Normale');
        $contenu = $reponse->getContent();
        $this->assertLessThan(strpos($contenu, 'Normale'), strpos($contenu, 'Urgente'), 'les annonces urgentes passent en premier');
    }

    public function test_announcement_image_is_optimised_and_removed_with_the_announcement(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->superAdmin()->create());

        $this->post('/gestion-annonces', $this->base + ['image' => UploadedFile::fake()->image('a.jpg')]);
        $annonce = Annonce::firstOrFail();
        Storage::disk('public')->assertExists($annonce->image_path);

        $this->delete("/gestion-annonces/{$annonce->id}");
        Storage::disk('public')->assertMissing($annonce->image_path);
    }

    public function test_announcements_page_can_be_switched_off(): void
    {
        Annonce::create($this->base + ['publie' => true]);
        $this->get('/annonces')->assertOk()->assertSee('Fermeture exceptionnelle');
        $this->get('/')->assertSee(route('site.annonces'), false);

        $this->actingAs(User::factory()->superAdmin()->create());
        $this->put('/parametres/application', ['nom_structure' => 'PPN', 'capacite_journaliere' => 60, 'visible_page_annonces' => 0]);

        $this->get('/annonces')->assertNotFound();
        $this->get('/')->assertDontSee(route('site.annonces'), false)->assertDontSee('Fermeture exceptionnelle');
    }

    public function test_sitemap_includes_announcements_page_when_active(): void
    {
        $this->get('/sitemap.xml')->assertSee(route('site.annonces'), false);
    }

    public function test_full_content_and_image_are_available_for_the_detail_popup(): void
    {
        Storage::fake('public');
        $texteLong = str_repeat('Ceci est le détail complet de l\'annonce, avec des informations importantes. ', 5);
        $annonce = Annonce::create([
            'titre' => 'Détails complets',
            'contenu' => $texteLong,
            'date_publication' => '2026-01-01',
            'publie' => true,
            'image_path' => 'annonces/photo.jpg',
        ]);
        $this->assertGreaterThan(160, strlen($texteLong));

        // Page /annonces (grille) : chaque carte porte le contenu complet en attribut, pas seulement l'extrait visible.
        $reponse = $this->get('/annonces')->assertOk();
        $reponse->assertSee('annonce-trigger', false);
        $reponse->assertSee('data-contenu="'.e($texteLong).'"', false);
        $reponse->assertSee('data-image', false);

        // Accueil (carrousel) : la carte porte aussi le contenu complet et l'image, pour la fenêtre de détail.
        $this->get('/')->assertSee('data-contenu="'.e($texteLong).'"', false)
            ->assertSee('data-image="'.Storage::disk('public')->url('annonces/photo.jpg').'"', false);
    }

    public function test_malicious_title_is_escaped_everywhere_it_is_rendered(): void
    {
        $piege = '<script>alert(1)</script>';
        Annonce::create(['titre' => $piege, 'contenu' => 'x', 'date_publication' => '2026-01-01', 'publie' => true]);

        // Ni la grille /annonces, ni le carrousel de l'accueil, ne doivent jamais renvoyer le script tel quel :
        // le titre doit toujours être échappé (que ce soit dans le texte visible ou dans data-titre).
        $this->get('/annonces')->assertDontSee($piege, false);
        $this->get('/')->assertDontSee($piege, false);
    }

    public function test_search_filters_announcements(): void
    {
        Annonce::create(['titre' => 'Fermeture exceptionnelle', 'contenu' => 'x', 'date_publication' => '2026-01-01', 'publie' => true]);
        Annonce::create(['titre' => 'Inscriptions ouvertes', 'contenu' => 'Formation bureautique disponible', 'date_publication' => '2026-01-01', 'publie' => true]);

        $this->get('/annonces?q=fermeture')->assertSee('Fermeture exceptionnelle')->assertDontSee('Inscriptions ouvertes');
        $this->get('/annonces?q=bureautique')->assertSee('Inscriptions ouvertes')->assertDontSee('Fermeture exceptionnelle');
        $this->get('/annonces?q=introuvable')->assertSee('Aucune annonce ne correspond');
    }

    public function test_announcements_page_has_a_print_button(): void
    {
        $this->get('/annonces')->assertOk()->assertSee('window.print()', false);
    }
}
