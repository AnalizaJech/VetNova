# 🚀 Guía de Deploy — VetNova en Railway (Gratis)

## ⚠️ Antes de empezar

Railway da **$5 USD de crédito gratis al mes**, suficiente para:

- 1 app Laravel (≈ $2-3/mes)
- 1 base de datos MySQL (≈ $1-2/mes)

No se necesita tarjeta de crédito para el plan Hobby gratuito.

---

## Paso 1 — Sube el proyecto a GitHub

Si aún no tienes el proyecto en GitHub:

```bash
# En la carpeta raíz de VetNova:
git init
git add .
git commit -m "Initial commit — VetNova"

# En github.com crea un repositorio privado llamado "vetnova"
# Luego conecta y sube:
git remote add origin https://github.com/TU_USUARIO/vetnova.git
git branch -M main
git push -u origin main
```

**Archivos que debes agregar a la raíz del proyecto antes del commit:**

- ✅ `nixpacks.toml` (incluido en esta carpeta)
- ✅ `railway.toml` (incluido en esta carpeta)

---

## Paso 2 — Crea cuenta en Railway

1. Ve a <https://railway.app>
2. Haz clic en **"Start a New Project"**
3. Inicia sesión con tu cuenta de **GitHub**

---

## Paso 3 — Crea el proyecto en Railway

1. En Railway → **"New Project"**
2. Elige **"Deploy from GitHub repo"**
3. Selecciona tu repositorio **vetnova**
4. Railway detectará automáticamente que es PHP/Laravel

---

## Paso 4 — Agrega MySQL

1. En tu proyecto Railway → **"+ New"** → **"Database"** → **"MySQL"**
2. Railway crea la base de datos y conecta automáticamente las variables

---

## Paso 5 — Configura las variables de entorno

En Railway → tu servicio Laravel → **"Variables"** → agrega una por una:

```env
APP_NAME=VetNova
APP_ENV=production
APP_DEBUG=false
APP_URL=https://TU-DOMINIO.up.railway.app   ← cambia después del deploy

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}               ← exactamente así, Railway lo resuelve
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}

CACHE_STORE=file
SESSION_DRIVER=file
SESSION_LIFETIME=120
QUEUE_CONNECTION=database
LOG_CHANNEL=stderr
LOG_LEVEL=error

PERUAPI_KEY=02dd8e7fce0843cda4d8398eb56f0cc3
PERUAPI_BASE_URL=https://peruapi.com
```

**Para generar APP_KEY:**

```bash
# En tu máquina local, corre:
php artisan key:generate --show
# Copia el resultado (base64:xxx...) y ponlo en Railway como APP_KEY
```

---

## Paso 6 — Deploy

1. Railway hace el deploy automáticamente al subir cambios a GitHub
2. El build puede tardar **3-5 minutos** la primera vez
3. Ve a **"Deployments"** para ver los logs en tiempo real

Si hay error, lee el log — casi siempre es una variable de entorno faltante.

---

## Paso 7 — Ejecutar migraciones (primera vez)

El `nixpacks.toml` ya corre `php artisan migrate --force` automáticamente al iniciar.

Si necesitas correrlo manualmente:

1. Railway → tu servicio → **"Settings"** → **"Deploy"** → sección **"Shell"**
2. O en el log del deploy verás si las migraciones se corrieron

---

## Paso 8 — Obtén la URL y actualiza PeruAPI ⭐

1. Railway → tu servicio → **"Settings"** → **"Networking"** → **"Generate Domain"**
2. Te da una URL como: `vetnova-production.up.railway.app`
3. **IMPORTANTE — IP de Railway:** Ve a la sección **"Public Networking"** o corre desde el shell:

   ```bash
   curl https://api.ipify.org
   ```

4. Ve a <https://peruapi.com/panel> → **"Gestión de IPs autorizadas"**
5. **Elimina tu IP local** (la de casa)
6. **Agrega la IP de Railway** (es fija, no cambia)

---

## Paso 9 — Corre el Seeder (si necesitas datos iniciales)

Desde Railway → Shell:

```bash
php artisan db:seed --class=ClinicaSeeder
php artisan db:seed --class=RolSeeder
php artisan db:seed --class=UbigeoSeeder
```

---

## 🛠️ Comandos útiles en Railway Shell

```bash
# Ver logs de Laravel
tail -f storage/logs/laravel.log

# Diagnóstico PeruAPI (nuevo comando incluido en el fix)
php artisan peruapi:diagnostico --dni=12345678

# Limpiar caché
php artisan cache:clear
php artisan config:clear

# Re-ejecutar migraciones
php artisan migrate --force

# Ver estado de la app
php artisan about
```

---

## ⚡ Tip: IP dinámica en casa (problema actual)

Dado que tu IP de casa cambia, tienes dos opciones:

### Opción A — Usar la IP de Railway (recomendado)

Deploy en Railway → IP fija → actualizar <https://peruapi.com> una sola vez ✅

### Opción B — Seguir en local con IP dinámica

Cada vez que cambie tu IP, entrar a <https://peruapi.com/panel> y actualizarla. Tedioso pero gratuito.

### Opción C — Proxy/VPN con IP fija

Usar un servicio como **Cloudflare Tunnel** o **ngrok** para tener una IP/URL fija en local.

---

## 💰 Costo estimado en Railway (plan gratis)

| Servicio | Costo/mes |
| :--- | :--- |
| App Laravel (512MB RAM) | ~$2.50 |
| MySQL (1GB) | ~$1.00 |
| **Total** | **~$3.50** |
| Crédito gratis | $5.00 |
| **A pagar** | **$0** ✅ |

El crédito alcanza con margen. Si crece el tráfico, puedes upgrade a $20/mes.

---

## 🆘 Problemas comunes

| Error | Causa | Solución |
| :--- | :--- | :--- |
| `APP_KEY not set` | Falta la variable | Genera con `php artisan key:generate --show` |
| `SQLSTATE: Connection refused` | Variables DB mal configuradas | Usa `${{MySQL.MYSQL_HOST}}` exactamente así |
| `DNI no encontrado` | IP de Railway no en peruapi.com | Actualiza la IP en peruapi.com/panel |
| `500 Server Error` | APP_DEBUG=false oculta el error | Pon APP_DEBUG=true temporalmente, luego revierte |
| Build falla en `npm run build` | Node no instalado | Está incluido en nixpacks.toml ✅ |
