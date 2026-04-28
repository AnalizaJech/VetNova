#!/bin/bash
# Script para el servicio de Worker (Cola de procesos)
echo "⚙️ Iniciando Queue Worker..."
php artisan queue:work --verbose --tries=3 --timeout=90
