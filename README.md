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

### Comptes initiaux

`php artisan db:seed` crée `admin@ppn-ande.fr` (Super Admin) et `agent@ppn-ande.fr` (Agent d'accueil).
**Il n'y a pas de mot de passe par défaut** : définissez `SEED_ADMIN_PASSWORD` et `SEED_AGENT_PASSWORD` dans le `.env`
avant le seed, sinon un mot de passe aléatoire est généré et affiché une seule fois dans la console.
La connexion est limitée à 10 tentatives par minute.

Le seed crée aussi quelques **activités et annonces d'exemple, volontairement en brouillon** (textes et dates inventés,
jamais des faits réels sur le PPN) : à relire, corriger avec du vrai contenu et publier vous-même avant l'ouverture au public,
ou à supprimer si vous préférez repartir de zéro.

## Site public

Le site vitrine est servi à la racine (`/`) : accueil, annonces (recherche), à propos, activités (recherche, filtres), agenda
mensuel, galerie photos/vidéos (recherche, filtres) et formulaire de contact. Les coordonnées, horaires, texte de présentation
et carte se règlent dans **Paramètres > Application** ; les annonces, activités et médias se gèrent dans le menu de l'espace
équipe, et les messages reçus dans **Messages**. Les pages d'une activité, des annonces et de la galerie ont un bouton
« Imprimer » (mise en page épurée, sans menu ni pied de page) pour un affichage sur panneau physique.

**Annonces** (menu « Annonces (site) ») : pour les informations courtes et datées — fermeture exceptionnelle, nouveaux horaires,
inscriptions ouvertes — distinctes des activités programmées. Une annonce cochée « urgente » est mise en avant en rouge, en tête
de la page Annonces et de l'accueil (carrousel automatique, pause au survol), et une annonce peut avoir une date d'expiration
après laquelle elle disparaît du site sans être supprimée.

**À propos** : la section « Le réseau des PPN de l'UVCI » (texte d'introduction, villes des zones urbaines, villes du milieu
rural) se modifie ou se complète dans **Paramètres > Application**, comme le reste de la présentation. « Andé » est mis en
valeur automatiquement dès qu'il figure dans la liste des villes rurales.

### Contrôle de ce qui est public

- Les **annonces**, **activités** et **médias** sont créés en **brouillon** : rien n'est visible du public tant que le Super Admin ne clique pas sur
  « Publier » (liste des annonces / des activités / des médias). Les ajouts d'un agent restent en brouillon jusqu'à validation ; l'agent peut consulter et ajouter, mais
  seul le Super Admin peut modifier, supprimer et publier.
- Un **compteur de visites anonyme** est affiché en bas du site (total et du jour), activable dans « Ce que le public peut voir ».
  Il ne conserve ni adresse IP ni identifiant : seul un total par jour est enregistré, et une session n'est comptée qu'une fois par jour.
  Les robots et les rechargements d'une même session peuvent gonfler un peu le chiffre. La politique de confidentialité le mentionne.
- Dans **Paramètres > Application > « Ce que le public peut voir »**, chaque page (Annonces, À propos, Agenda, Galerie, Contact) et chaque
  information de contact (adresse, téléphones, e-mail, horaires, carte) peut être masquée sans être effacée.

### Validation, notifications et pages légales

- Ce qu'un agent ajoute est marqué **« à valider »** : un badge apparaît dans le menu du Super Admin et un **e-mail** lui est envoyé
  (il part réellement dès qu'un fournisseur SMTP est configuré ; avec `MAIL_MAILER=log` il est seulement écrit dans `storage/logs`).
  Un échec d'envoi ne bloque jamais l'action de l'utilisateur. Publier une activité publie aussi les photos et vidéos soumises avec elle.
- Chaque nouveau message du formulaire de contact déclenche aussi un e-mail aux Super Admin.
- Les photos téléversées sont **redimensionnées** (1600 px max) et recompressées automatiquement (orientation des photos de téléphone respectée).
- Pages **Mentions légales** et **Politique de confidentialité** : texte type (loi ivoirienne n° 2013-450, ARTCI) complété depuis
  Paramètres > Application. **À faire relire par le responsable du PPN**, ou à remplacer par le texte officiel dans les mêmes réglages.
  Le formulaire de contact exige le consentement de l'utilisateur.
- `/sitemap.xml` et `/robots.txt` sont générés automatiquement (uniquement les pages et activités publiques).

## Mise en production

1. Partir de `.env.production.example` : `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true` (HTTPS), compte MySQL dédié.
2. `composer install --no-dev --optimize-autoloader`, `php artisan key:generate`, `php artisan migrate --force`.
3. `php artisan storage:link` (nécessaire pour afficher les images et vidéos téléversées).
4. `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
5. Définir des mots de passe forts (voir ci-dessus) et ne jamais laisser `APP_DEBUG=true`.
6. Les vidéos téléversées sont limitées à 50 Mo par l'application ; adapter `upload_max_filesize` et `post_max_size` de PHP en conséquence
   (ou utiliser un lien YouTube/Vimeo).

## Modules

| Module | Accès Super Admin | Accès Agent |
|---|---|---|
| Dashboard, Statistiques | ✓ | ✓ |
| Visiteurs (créer/modifier/exporter) | ✓ | ✓ |
| Visiteurs (supprimer) | ✓ | — |
| Établissements (consulter) | ✓ | ✓ |
| Établissements (créer/modifier/supprimer) | ✓ | — |
| Calendrier (consulter) | ✓ | ✓ |
| Calendrier (créer/modifier) | ✓ | ✓ |
| Calendrier (supprimer) | ✓ | — |
| Annonces, activités et médias du site (consulter, ajouter en brouillon) | ✓ | ✓ |
| Annonces, activités et médias du site (modifier, supprimer, publier) | ✓ | — |
| Messages du formulaire de contact (lire) | ✓ | ✓ |
| Messages du formulaire de contact (supprimer) | ✓ | — |
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
