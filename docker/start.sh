#!/bin/sh
# Démarrage du conteneur : prépare Laravel puis lance le serveur sur $PORT.
set -e
cd /app

# Lien public/storage (photos et vidéos téléversées). Ignoré s'il existe déjà.
php artisan storage:link --force >/dev/null 2>&1 || true

# Migrations : si la base est injoignable, on échoue ici plutôt que de servir des 500.
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo "[start] php artisan migrate --force"
    php artisan migrate --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[start] écoute sur 0.0.0.0:${PORT:-8080}"
exec frankenphp php-server --root public/ --listen "0.0.0.0:${PORT:-8080}"
