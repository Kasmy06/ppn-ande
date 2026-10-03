<?php

namespace App\Support;

use App\Models\ParametreApplication;

/**
 * Coordonnées et textes du site public, modifiables dans Paramètres > Application.
 * Chaque information et chaque page peut être masquée au public sans être effacée.
 */
class SiteInfo
{
    /** Informations de contact que l'équipe peut afficher ou masquer. */
    public const INFOS = [
        'adresse' => 'Adresse',
        'telephones' => 'Numéros de téléphone',
        'email' => 'Adresse e-mail',
        'horaires' => 'Horaires d\'ouverture',
        'carte' => 'Carte',
        'compteur' => 'Compteur de visites',
    ];

    /** Pages du site public que l'équipe peut activer ou désactiver. */
    public const PAGES = [
        'annonces' => 'Annonces',
        'a_propos' => 'À propos',
        'agenda' => 'Agenda',
        'galerie' => 'Galerie photos & vidéos',
        'contact' => 'Contact (formulaire et coordonnées)',
        'mentions' => 'Mentions légales',
        'confidentialite' => 'Politique de confidentialité',
    ];

    /** Une information/page est visible tant que l'équipe ne l'a pas explicitement masquée. */
    public static function visible(string $cle): bool
    {
        return ParametreApplication::get('visible_'.$cle, '1') !== '0';
    }

    public static function page(string $nom): bool
    {
        return self::visible('page_'.$nom);
    }

    /** @return string[] */
    public static function telephones(): array
    {
        if (! self::visible('telephones')) {
            return [];
        }
        $brut = (string) ParametreApplication::get('telephones', '');

        return array_values(array_filter(array_map('trim', preg_split('/\R/', $brut))));
    }

    public static function lienTel(string $numero): string
    {
        return 'tel:'.preg_replace('/[^\d+]/', '', $numero);
    }

    public static function adresse(): ?string
    {
        return self::visible('adresse') ? (ParametreApplication::get('adresse') ?: null) : null;
    }

    public static function email(): ?string
    {
        return self::visible('email') ? (ParametreApplication::get('email') ?: null) : null;
    }

    public static function horaires(): ?string
    {
        return self::visible('horaires') ? (ParametreApplication::get('horaires') ?: null) : null;
    }

    /** Compteur anonyme de visites du site public (voir CompterVisite), affiché si l'équipe le souhaite. */
    public static function compteurVisible(): bool
    {
        return self::visible('compteur');
    }

    public static function visitesTotal(): int
    {
        return (int) \Illuminate\Support\Facades\DB::table('visites_journalieres')->sum('nombre');
    }

    public static function visitesAujourdhui(): int
    {
        return (int) \Illuminate\Support\Facades\DB::table('visites_journalieres')->where('jour', today()->toDateString())->value('nombre');
    }

    public static function nom(): string
    {
        return ParametreApplication::get('nom_structure', "PPN d'Andé");
    }

    public static function responsable(): ?string
    {
        return ParametreApplication::get('responsable') ?: null;
    }

    public static function hebergeur(): ?string
    {
        return ParametreApplication::get('hebergeur') ?: null;
    }

    /** Texte juridique personnalisé (remplace le texte type) : 'mentions_legales' ou 'confidentialite'. */
    public static function texte(string $cle): ?string
    {
        return ParametreApplication::get($cle) ?: null;
    }

    public static function dureeConservation(): string
    {
        return ParametreApplication::get('duree_conservation') ?: '12 mois';
    }

    public static function aPropos(): ?string
    {
        return ParametreApplication::get('a_propos') ?: null;
    }

    /** Présentation du réseau des PPN (page « À propos »), modifiable dans Paramètres > Application. */
    public static function reseauTexte(): string
    {
        return ParametreApplication::get('reseau_texte') ?: "Les Points de Présence Numérique (PPN) de l'Université Virtuelle de Côte d'Ivoire (UVCI) sont des centres physiques locaux créés pour rapprocher l'université numérique des étudiants et des populations, tant en zone urbaine que rurale.";
    }

    public static function reseauVillesUrbaines(): string
    {
        return ParametreApplication::get('reseau_villes_urbaines') ?: 'Cocody, Koumassi, Abobo (Nord et Sud), Bouaké, Grand-Bassam';
    }

    public static function reseauVillesRurales(): string
    {
        return ParametreApplication::get('reseau_villes_rurales') ?: 'Dingouin, Andé, Moofoué';
    }

    /** Adresse d'intégration (iframe) Google Maps ou OpenStreetMap, seulement si elle est reconnue et affichée. */
    public static function carteUrl(): ?string
    {
        if (! self::visible('carte')) {
            return null;
        }
        $url = ParametreApplication::get('carte_url');

        return $url && self::carteValide($url) ? $url : null;
    }

    public static function carteValide(string $url): bool
    {
        return (bool) preg_match('~^https://(www\.google\.com/maps/embed|maps\.google\.com/maps|www\.openstreetmap\.org/export/embed\.html)~', $url);
    }
}
