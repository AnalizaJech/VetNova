#!/bin/bash
# Script de inicio para la aplicación principal en Railway
# Este script se debe ejecutar en el "Pre-Deploy Command" o como parte del Start Command

echo "🚀 Iniciando preparación de la aplicación..."

# Ejecutar migraciones de base de datos
echo "🔄 Ejecutando migraciones..."
php artisan migrate --force

echo "✅ Aplicación lista para recibir tráfico."
