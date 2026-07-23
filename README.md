# PPN d'Andé — Plateforme de Gestion Statistique

Application Laravel de gestion et de suivi de fréquentation du **Point de Présence Numérique d'Andé** :
visiteurs (élèves, fonctionnaires, externes), établissements partenaires, calendrier de réservations,
statistiques et exports.

Voir le [cahier des charges](../PPN%20Ande%20front%20end/cahier-des-charges.md) pour le détail fonctionnel du projet.

## Stack technique

- Laravel 12 / PHP 8.2, MySQL
- Blade (monolithe, pas de front-end séparé), authentification par session
- [maatwebsite/excel](https://github.com/SpartnerNL/Laravel-Excel) pour les exports XLSX
- [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf) pour les exports PDF

## Installation locale (XAMPP)

```bash
composer install
copy .env.example .env        # puis configurer DB_* pour pointer vers votre base MySQL locale
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

Ouvrir `http://127.0.0.1:8000`.

### Comptes de démonstration (seeder)

| Rôle | Email | Mot de passe |
|---|---|---|
| Super Admin | `admin@ppn-ande.fr` | `password` |
| Agent d'accueil | `agent@ppn-ande.fr` | `password` |

**Ces identifiants sont à usage de développement uniquement — à changer avant toute mise en production.**

## Modules

| Module | Accès Super Admin | Accès Agent |
|---|---|---|
| Dashboard, Statistiques | ✓ | ✓ |
| Visiteurs (créer/modifier/exporter) | ✓ | ✓ |
| Visiteurs (supprimer) | ✓ | — |
| Établissements (consulter) | ✓ | ✓ |
| Établissements (créer/modifier/supprimer) | ✓ | — |
| Calendrier (consulter) | ✓ | ✓ |
| Calendrier (créer/modifier/supprimer) | ✓ | — |
| Exports | ✓ | ✓ |
| Paramètres (utilisateurs, config, journal) | ✓ | — |

Le contrôle d'accès est appliqué côté serveur (middleware `role:super_admin`), pas seulement dans l'interface.

## Tests automatisés

```bash
php artisan test
```

Utilise SQLite en mémoire (configuré dans `phpunit.xml`), aucune base de données réelle n'est nécessaire
pour lancer les tests.

## Déploiement en production

1. Copier `.env.production.example` vers `.env` sur le serveur et compléter toutes les valeurs
   (clé d'application, base de données, SMTP pour l'envoi réel des emails de réinitialisation de mot de passe).
2. `APP_ENV=production` et `APP_DEBUG=false` sont **obligatoires** — sinon les erreurs affichent la pile
   d'exécution complète aux visiteurs du site.
3. Générer la clé et optimiser :
   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
4. **Changer immédiatement le mot de passe du compte `admin@ppn-ande.fr`** (ou créer un nouveau compte
   Super Admin puis supprimer/désactiver celui-ci) — voir Paramètres > Utilisateurs.
5. Configurer un vrai fournisseur SMTP (`MAIL_MAILER`) : avec la valeur `log` utilisée en développement,
   les emails de réinitialisation de mot de passe ne partent jamais réellement, ils sont seulement écrits
   dans `storage/logs/laravel.log`.
6. RGPD : ce point n'est pas couvert par le code — mentions légales, durée de conservation des données
   des mineurs et désignation d'un responsable de traitement restent à définir avec le porteur du projet.

## Modules non couverts (hors périmètre actuel)

Aucun à ce jour côté fonctionnalités du cahier des charges — l'ensemble des 8 modules (Dashboard, Visiteurs,
Établissements, Statistiques, Calendrier, Exports, Paramètres, Aide) est implémenté. Restent, en dehors du
code applicatif : l'hébergement de production, le nom de domaine définitif, et la conformité RGPD.
