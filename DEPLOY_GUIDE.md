# 🚀 Guía de Deploy Profesional — VetNova en Railway

Siguiendo la documentación oficial de Railway para Laravel, aquí tienes los pasos exactos para corregir el error de compilación y desplegar con éxito.

## ⚠️ El problema actual
<<<<<<< HEAD

=======
>>>>>>> ef7ca835c991d56e3c6dd598e32a8e53cf8528b4
El error `Docker build failed` ocurre porque se está intentando ejecutar `php artisan migrate` durante la fase de **Build** (construcción). En esa fase, la base de datos no es accesible.

---

## Paso 1 — Limpieza de Variables en Railway
<<<<<<< HEAD

Antes de volver a intentar el deploy, entra al panel de **Railway → Variables** y:

1. **ELIMINA** la variable `NIXPACKS_BUILD_CMD` si la habías agregado manualmente.
2. Asegúrate de que tus variables de base de datos apunten a los valores internos de Railway (ej: `${{MySQL.MYSQL_HOST}}`).

---

## Paso 2 — Estructura de Scripts de Inicialización

He creado una carpeta `/railway` con scripts específicos para cada servicio (Monolito Majestuoso):

* `railway/init-app.sh`: Ejecuta las migraciones antes de iniciar la web.
* `railway/run-worker.sh`: Para el servicio de colas (opcional).
* `railway/run-cron.sh`: Para el programador de tareas (opcional).

---

## Paso 3 — Configuración en el Panel de Railway

Para que las migraciones funcionen sin romper el build, configúralas así en la interfaz de Railway:

1. Ve a **Settings** → **Deploy**.
2. Busca el campo **Pre-Deploy Command**.
3. Pega exactamente esto:

```bash
chmod +x ./railway/init-app.sh && ./railway/init-app.sh
```

*Esto ejecutará las migraciones justo después de construir la imagen pero ANTES de encender el servidor.*

---

## Paso 4 — Variables de Entorno Críticas

=======
Antes de volver a intentar el deploy, entra al panel de **Railway → Variables** y:
1.  **ELIMINA** la variable `NIXPACKS_BUILD_CMD` si la habías agregado manualmente.
2.  Asegúrate de que tus variables de base de datos apunten a los valores internos de Railway (ej: `${{MySQL.MYSQL_HOST}}`).

---

## Paso 2 — Estructura de Scripts de Inicialización
He creado una carpeta `/railway` con scripts específicos para cada servicio (Monolito Majestuoso):

*   `railway/init-app.sh`: Ejecuta las migraciones antes de iniciar la web.
*   `railway/run-worker.sh`: Para el servicio de colas (opcional).
*   `railway/run-cron.sh`: Para el programador de tareas (opcional).

---

## Paso 3 — Configuración en el Panel de Railway
Para que las migraciones funcionen sin romper el build, configúralas así en la interfaz de Railway:

1.  Ve a **Settings** → **Deploy**.
2.  Busca el campo **Pre-Deploy Command**.
3.  Pega exactamente esto:
    ```bash
    chmod +x ./railway/init-app.sh && ./railway/init-app.sh
    ```
    *Esto ejecutará las migraciones justo después de construir la imagen pero ANTES de encender el servidor.*

---

## Paso 4 — Variables de Entorno Críticas
>>>>>>> ef7ca835c991d56e3c6dd598e32a8e53cf8528b4
Asegúrate de tener estas variables en Railway para producción:

```env
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=stderr
LOG_STDERR_FORMATTER=\Monolog\Formatter\JsonFormatter
DB_CONNECTION=mysql
APP_KEY=base64:xxx... (Generada localmente)
```

---
<<<<<<< HEAD

## Paso 5 — Soporte para Múltiples Servicios (Opcional)

Si quieres separar el Worker (colas) del servidor Web:

1. Crea un nuevo servicio en Railway desde el mismo repo.
2. En **Settings** → **Deploy** → **Start Command**, pon:
=======
>>>>>>> ef7ca835c991d56e3c6dd598e32a8e53cf8528b4

## Paso 5 — Soporte para Múltiples Servicios (Opcional)
Si quieres separar el Worker (colas) del servidor Web:
1.  Crea un nuevo servicio en Railway desde el mismo repo.
2.  En **Settings** → **Deploy** → **Start Command**, pon:
    ```bash
    chmod +x ./railway/run-worker.sh && ./railway/run-worker.sh
    ```

---

## 🛠️ Comandos de Diagnóstico en Railway Shell
Si algo falla, abre la **Shell** del servicio en Railway y usa:
```bash
<<<<<<< HEAD
chmod +x ./railway/run-worker.sh && ./railway/run-worker.sh
```

---

## 🛠️ Comandos de Diagnóstico en Railway Shell

Si algo falla, abre la **Shell** del servicio en Railway y usa:

```bash
# Probar conexión a la base de datos
php artisan db:show

# Ver qué migraciones faltan
php artisan migrate:status

# Limpiar todas las cachés
php artisan optimize:clear
```

---

=======
# Probar conexión a la base de datos
php artisan db:show

# Ver qué migraciones faltan
php artisan migrate:status

# Limpiar todas las cachés
php artisan optimize:clear
```

---
>>>>>>> ef7ca835c991d56e3c6dd598e32a8e53cf8528b4
**VetNova** está ahora alineado con la arquitectura recomendada por Railway para apps Laravel modernas.
