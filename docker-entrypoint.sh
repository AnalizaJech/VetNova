#!/bin/bash
set -e

# ============================================================
# docker-entrypoint.sh — Script de arranque para Railway
# ============================================================

echo "🚀 Iniciando VetNova..."

# Railway asigna $PORT dinámicamente (default 80)
PORT="${PORT:-80}"

# Configurar Apache para escuchar en el puerto de Railway
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Optimizar Laravel para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Crear tablas de sesión/cache/queue si usan DB
# (no falla si ya existen)
php artisan migrate --force --no-interaction

echo "✅ Migraciones completadas"

# Arrancar queue worker en background
php artisan queue:work --daemon --tries=3 --sleep=3 &

echo "✅ Queue worker iniciado"
echo "🌐 Apache escuchando en puerto ${PORT}"

# Arrancar Apache en foreground
exec apache2-foreground
