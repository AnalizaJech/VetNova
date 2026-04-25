# VetNova — Software de Gestión Veterinaria (SaaS)

## Objetivo
Proveer un sistema integral, rápido y fácil de usar para clínicas veterinarias en Perú, permitiendo gestionar en un solo lugar la agenda de citas, el historial médico, el control de inventario preventivo y la emisión de comprobantes electrónicos de pago (SUNAT).

## Problema que resuelve
Las clínicas veterinarias a menudo operan con sistemas desconectados (Excel para el inventario, papel para historias clínicas, y portales lentos para facturar). VetNova unifica esto en un entorno multi-tenant automatizando flujos críticos, como el descuento inmediato de inventario al vender y el auto-completado de DNI/RUC para acelerar la recepción.

## Tecnologías
- **Backend:** Laravel 11, PHP 8.3
- **Frontend:** Livewire 3, Alpine.js, Tailwind CSS v4, Mary UI
- **Base de Datos:** MySQL 8 / MariaDB
- **Integraciones:** PeruAPI (para consulta rápida de DNI/RUC) y Nubefact (para facturación electrónica a SUNAT)

## Arquitectura
- **Multi-tenancy Lógico:** Todos los registros (ventas, citas, clientes, mascotas) están rigurosamente filtrados por un `clinica_id` a nivel de base de datos.
- **Frontend Reactivo:** Se utiliza Livewire para mantener un flujo similar a una SPA (Single Page Application), permitiendo validaciones asíncronas y renderizado rápido sin recargar la página.
- **Servicios Aislados:** Clases dedicadas en `app/Services` (`NubefactService`, `PeruApiService`) que abstraen la complejidad y cambios de las APIs de terceros, protegiendo los controladores principales.

---

## Instalación y Configuración Local

Sigue estos pasos para desplegar el proyecto localmente.

### 1. Clonar e Instalar Dependencias
```bash
git clone https://github.com/AnalizaJech/VetNova.git
cd VetNova
composer install
npm install
```

### 2. Variables de Entorno
Copia el archivo base y genera la clave de tu aplicación.
```bash
cp .env.example .env
php artisan key:generate
```

Configura tu base de datos y agrega los tokens de los servicios en el `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vetnova
DB_USERNAME=root
DB_PASSWORD=

# APIs
PERUAPI_KEY="tu_token_peruapi_aqui"
NUBEFACT_URL="https://api.nubefact.com/api/v1/.../invoice"
NUBEFACT_TOKEN="tu_token_nubefact_aqui"
```

## Base de Datos

El sistema utiliza exclusivamente MySQL/MariaDB. En la carpeta `/database` se encuentra el archivo `database.sql` que contiene la estructura final del esquema de la base de datos (con Kardex, Prescripciones y tablas de seguridad). 

> **Nota:** Debido a que VetNova utiliza vistas y triggers complejos en versiones futuras, la regla de oro para desplegar en un entorno nuevo es importar directamente el SQL proporcionado o ejecutar las migraciones frescas.

```bash
# Opción 1: Migrar y sembrar datos base (Recomendado)
php artisan migrate:fresh --seed
```

*(Si necesitas exportar la estructura manualmente, usa: `mysqldump -u root vetnova > database/database.sql` en la terminal de tu servidor de BD).*

### Usuarios de Prueba
Tras la migración, usa estas credenciales para entrar al sistema como `super_admin`:
- **Correo:** `admin@vetnova.pe`
- **Contraseña:** `password`

## Compilar e Iniciar el Servidor

Para que el frontend funcione correctamente (compilando las clases de Tailwind de MaryUI):
```bash
# Compilar vistas y estilos una sola vez (Recomendado para testing rápido)
npm run build

# O en entorno de desarrollo para escuchar cambios
npm run dev
```

Finalmente, levanta el servidor web:
```bash
php artisan serve
```
El sistema estará disponible en `http://localhost:8000`.

---

## Decisiones Técnicas Destacadas
- **SPA Liviana:** Se eligió el ecosistema de **Livewire + Mary UI** sobre Next.js/React para eliminar la sobrecarga de mantener dos repositorios separados. Se alcanza una UX premium asíncrona pero manejada directamente con la potencia de PHP.
- **Buscadores Asíncronos:** Módulos críticos como la *Caja (POS)* y *Citas* utilizan `x-choices` asíncronos para evitar colapsos de memoria.
- **Tickets Térmicos vía Web:** Se optó por usar `@media print` directo desde Blade en vez de librerías pesadas como DOMPDF, esto permite invocar a `window.print()` nativamente y adaptar tickets instantáneos para tiqueteras de 80mm.
- **Seguridad Multi-Tenant Estricta:** Las rutas, scopes de Livewire y validaciones `OrFail` siempre incluyen `where('clinica_id', ...)` acoplado a middleware de permisos Spatie Role/Permission.
- **Control de Concurrencia:** La lógica de caja implementa bloqueos de base de datos pesimistas (`lockForUpdate`) para evitar ventas de stock negativo bajo alta concurrencia.

## 🏥 Módulos Principales
1. **Punto de Venta (Caja) y Facturación Inteligente**: Integración con Nubefact, cálculo de IGV, control de caja y emisión.
2. **Historias Clínicas, Triaje y Prescripciones**: Flujo médico completo. Las prescripciones se entrelazan con el inventario para facilitar el despacho en caja.
3. **Control de Vacunas**: Calendario de registro preventivo (vacunas, antipulgas).
4. **Inventario Híbrido y Kardex Inmutable**: Control dual de "Productos" y "Servicios". Cada compra, venta o ajuste manual se audita automáticamente en un *Kardex inmutable* de control logístico.
5. **Hospitalización (Internamiento)**: Panel de control visual de camas, bitácora de evolución médica (notas) y sistema de altas.
6. **Centro de Recordatorios**: Panel automatizado para gestionar las citas y vacunas pendientes del día (preparado para integración Twilio WhatsApp).
7. **Panel de Configuración y Seguridad**: CRUD de usuarios internos con asignación de roles jerárquicos (Spatie) y administración general de la clínica/sucursales.
8. **Reportes y Analíticas**: Dashboard gerencial con Chart.js para medir KPIs en tiempo real.

## 🚀 Futuras Mejoras 
- Portal de auto-servicio para que los clientes vean las recetas de sus mascotas.
- Integración contable para multi-cajas simultáneas.
- Implementación total del flujo E2E Testing (Pest + Playwright).
