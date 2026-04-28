#!/bin/bash
# Script para el servicio de Cron (Tareas programadas)
echo "⏰ Iniciando Scheduler..."
while [ true ]
do
  php artisan schedule:run --no-interaction &
  sleep 60
done
