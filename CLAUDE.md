# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

PPN d'Andé: a Laravel 12 / PHP 8.2 monolith (Blade views, session auth, MySQL) that does two things in one app:
an internal team space (visitor register, partner schools, reservation calendar, statistics, XLSX/PDF exports)
and a public showcase site served at `/`. UI text, comments, routes, table/column names and commit messages are in **French**; keep to that.
`README.md` has the install, production-deployment and role-matrix details.

## Commands

```bash
composer install && cp .env.example .env && php artisan key:generate
php artisan migrate && php artisan db:seed   # seed needs SEED_ADMIN_PASSWORD / SEED_AGENT_PASSWORD, else random passwords are printed once
php artisan serve

php artisan test                              # SQLite in-memory (phpunit.xml), no DB needed
php artisan test tests/Feature/AnnonceTest.php
php artisan test --filter=nom_du_test
vendor/bin/pint                               # code style
```

`composer dev` runs server + queue + pail + vite together (requires npm). Vite is only the Laravel default scaffold; views are plain Blade.

## Architecture

- **Routes** (`routes/web.php`) have three tiers: public site (`SiteController`, no auth), `guest` (login / password reset), and `auth`.
  Inside `auth`, a nested `role:super_admin` group holds edit/delete/publish and Paramètres routes.
  The `role` middleware is `EnsureUserHasRole`; roles are `super_admin` and `agent`. Permissions are enforced server-side, so new
  write routes must go in the right group.
- **Draft/publish workflow** for `Activite`, `Annonce` and `Media`: everything is created as a draft, and only a Super Admin can publish
  (`basculerPublication`). An agent's additions are flagged `a_valider`, which triggers an in-app badge and an e-mail via `app/Support/Notifier.php`
  (mail failures must never block the user's action). Publishing an activity also publishes the media submitted with it. Public queries must filter on published state.
- **Public site visibility** is configurable at runtime: `app/Support/SiteInfo.php` reads `ParametreApplication` key/value settings
  (`visible_*`, `visible_page_*`) so each public page and contact detail can be hidden without deleting content. Gate new public pages/info
  through `SiteInfo::PAGES` / `SiteInfo::INFOS`. Contact info, hours, About text and legal texts are all edited in Paramètres > Application.
- **Other helpers**: `app/Support/ImageOptimizer.php` resizes uploads (1600px max); `app/Exports/VisiteursExport.php` (maatwebsite/excel) and
  dompdf (`resources/views/exports/visiteurs-pdf.blade.php`) back the exports; `JournalActivite` is the audit log shown in Paramètres.
- **Views**: `layouts/app.blade.php` is the team space, `layouts/site.blade.php` the public site; public views live in `resources/views/site/`.
- **Tests** are in `tests/Feature` (by area: `Auth`, `Roles`, `Visiteurs`, `Calendrier`, `Etablissements` plus top-level files for the public site, publication, announcements, gallery).
