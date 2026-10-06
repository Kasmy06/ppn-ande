# syntax=docker/dockerfile:1

# --- Étape 1 : dépendances PHP --------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# Scripts désactivés ici : le code applicatif n'est pas encore copié.
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist \
    --no-interaction --ignore-platform-reqs

# --- Étape 2 : image finale (FrankenPHP) ---------------------------------------
# Pas d'étape Node : les vues publiques et l'espace équipe n'utilisent pas Vite.
FROM dunglas/frankenphp:1-php8.3-bookworm AS app
WORKDIR /app

# gd + zip + exif : exports XLSX/PDF et redimensionnement des photos téléversées.
RUN install-php-extensions pdo_mysql gd exif intl bcmath zip opcache \
    && apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=vendor /app/vendor ./vendor
COPY . .

RUN composer dump-autoload --no-dev --optimize --classmap-authoritative \
    && mkdir -p storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs storage/app/public bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache \
    && chmod +x docker/start.sh \
    && mkdir -p /data/caddy /config/caddy \
    && chown -R www-data:www-data /data /config

# Vidéos jusqu'à 50 Mo : PHP doit accepter des requêtes plus grosses que ses 2/8 Mo par défaut.
RUN printf 'upload_max_filesize=64M\npost_max_size=70M\n' > /usr/local/etc/php/conf.d/uploads.ini

# Le hoster fournit PORT ; HTTP simple (TLS terminé par le reverse proxy).
ENV PORT=8080 \
    APP_ENV=production \
    SERVER_NAME=:8080
EXPOSE 8080

# Teste l'app elle-même (route /up de Laravel), pas l'API d'admin de Caddy.
HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD curl -fsS "http://127.0.0.1:${PORT:-8080}/up" >/dev/null || exit 1

USER www-data
CMD ["docker/start.sh"]
