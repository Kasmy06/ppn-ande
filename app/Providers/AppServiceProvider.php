<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Les liens vers les fichiers téléversés suivent l'adresse réellement utilisée
        // (artisan serve, XAMPP en sous-dossier…) au lieu de dépendre de APP_URL.
        config(['filesystems.disks.public.url' => asset('storage')]);
    }
}
