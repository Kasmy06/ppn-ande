<?php

use App\Http\Controllers\ActiviteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EtablissementController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\StatistiqueController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisiteurController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'accueil'])->name('site.accueil');
Route::get('/activites', [SiteController::class, 'activites'])->name('site.activites');
Route::get('/galerie', [SiteController::class, 'galerie'])->name('site.galerie');
Route::get('/agenda', [SiteController::class, 'agenda'])->name('site.agenda');
Route::get('/a-propos', [SiteController::class, 'aPropos'])->name('site.a-propos');
Route::get('/mentions-legales', [SiteController::class, 'mentions'])->name('site.mentions');
Route::get('/confidentialite', [SiteController::class, 'confidentialite'])->name('site.confidentialite');
Route::get('/sitemap.xml', [SiteController::class, 'sitemap'])->name('site.sitemap');
Route::get('/robots.txt', [SiteController::class, 'robots'])->name('site.robots');
Route::get('/contact', [SiteController::class, 'contact'])->name('site.contact');
Route::post('/contact', [SiteController::class, 'envoyerContact'])->middleware('throttle:5,10')->name('site.contact.envoyer');
Route::get('/activites/{activite}', [SiteController::class, 'activite'])->whereNumber('activite')->name('site.activite');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');

    Route::get('/mot-de-passe/oublie', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/mot-de-passe/oublie', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/mot-de-passe/reinitialiser/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/mot-de-passe/reinitialiser', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/visiteurs', [VisiteurController::class, 'index'])->name('visiteurs.index');
    Route::get('/visiteurs/export', [VisiteurController::class, 'export'])->name('visiteurs.export');
    Route::get('/visiteurs/rechercher-existant', [VisiteurController::class, 'rechercherExistant'])->name('visiteurs.rechercher');
    Route::get('/visiteurs/fiche', [VisiteurController::class, 'fiche'])->name('visiteurs.fiche');
    Route::post('/visiteurs', [VisiteurController::class, 'store'])->name('visiteurs.store');
    Route::put('/visiteurs/{visiteur}', [VisiteurController::class, 'update'])->name('visiteurs.update');

    Route::get('/etablissements', [EtablissementController::class, 'index'])->name('etablissements.index');

    Route::get('/statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');

    Route::get('/calendrier', [ReservationController::class, 'index'])->name('calendrier.index');
    Route::post('/calendrier', [ReservationController::class, 'store'])->name('calendrier.store');
    Route::put('/calendrier/{reservation}', [ReservationController::class, 'update'])->name('calendrier.update');

    Route::get('/exports', [ExportController::class, 'index'])->name('exports.index');

    Route::get('/aide', fn () => view('aide'))->name('aide');

    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');

    // Photos & vidéos : consultation et ajout ouverts à l'équipe (modification/suppression : Super Admin).
    Route::get('/gestion-medias', [MediaController::class, 'index'])->name('medias.index');
    Route::get('/gestion-medias/create', [MediaController::class, 'create'])->name('medias.create');
    Route::post('/gestion-medias', [MediaController::class, 'store'])->name('medias.store');

    // Activités : consultation et ajout ouverts à l'équipe (l'ajout d'un agent reste en brouillon ; le reste : Super Admin).
    Route::get('/gestion-activites', [ActiviteController::class, 'index'])->name('activites.index');
    Route::get('/gestion-activites/create', [ActiviteController::class, 'create'])->name('activites.create');
    Route::post('/gestion-activites', [ActiviteController::class, 'store'])->name('activites.store');

    // Messages du formulaire de contact : consultation par toute l'équipe, suppression réservée au Super Admin.
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::patch('/messages/{message}/lu', [MessageController::class, 'basculerLu'])->name('messages.lu');

    // Actions réservées au Super Admin.
    Route::middleware('role:super_admin')->group(function () {
        Route::delete('/messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');

        Route::patch('/gestion-activites/{activite}/publication', [ActiviteController::class, 'basculerPublication'])->name('activites.publication');
        Route::patch('/gestion-medias/{media}/publication', [MediaController::class, 'basculerPublication'])->name('medias.publication');

        Route::resource('gestion-activites', ActiviteController::class)
            ->only(['edit', 'update', 'destroy'])->parameters(['gestion-activites' => 'activite'])->names('activites');
        Route::resource('gestion-medias', MediaController::class)
            ->only(['edit', 'update', 'destroy'])->parameters(['gestion-medias' => 'media'])->names('medias');

        Route::delete('/visiteurs/{visiteur}', [VisiteurController::class, 'destroy'])->name('visiteurs.destroy');

        Route::post('/etablissements', [EtablissementController::class, 'store'])->name('etablissements.store');
        Route::put('/etablissements/{etablissement}', [EtablissementController::class, 'update'])->name('etablissements.update');
        Route::delete('/etablissements/{etablissement}', [EtablissementController::class, 'destroy'])->name('etablissements.destroy');

        Route::delete('/calendrier/{reservation}', [ReservationController::class, 'destroy'])->name('calendrier.destroy');

        Route::prefix('parametres')->name('parametres.')->group(function () {
            Route::get('/utilisateurs', [UserController::class, 'index'])->name('utilisateurs.index');
            Route::post('/utilisateurs', [UserController::class, 'store'])->name('utilisateurs.store');
            Route::put('/utilisateurs/{user}', [UserController::class, 'update'])->name('utilisateurs.update');
            Route::delete('/utilisateurs/{user}', [UserController::class, 'destroy'])->name('utilisateurs.destroy');

            Route::get('/application', [ParametreController::class, 'edit'])->name('application.edit');
            Route::put('/application', [ParametreController::class, 'update'])->name('application.update');
            Route::delete('/application/logo', [ParametreController::class, 'supprimerLogo'])->name('application.logo.destroy');

            Route::get('/journal', [JournalController::class, 'index'])->name('journal.index');
        });
    });
});
