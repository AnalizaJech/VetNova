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

Asegúrate de crear la base de datos `vetnova` vacía en MySQL. Luego, ejecuta las migraciones y seeders. El seeder preparará el entorno inicial poblando los ubigeos del Perú, creando roles base y generando la primera clínica.

```bash
php artisan migrate --seed
```

### Usuarios de Prueba
Tras la migración, usa estas credenciales para entrar al sistema:
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
- **Buscadores Asíncronos:** Módulos críticos como la *Caja (POS)* y *Citas* utilizan `x-choices` asíncronos. En vez de enviar listas enteras al cliente (lo cual colapsaría el navegador si la veterinaria tiene 10,000 productos), la base de datos hace las búsquedas en tiempo real.
- **Tickets Térmicos vía Web:** Se optó por usar `@media print` directo desde Blade en vez de librerías pesadas como DOMPDF, esto permite invocar a `window.print()` nativamente y adaptar tickets instantáneos perfectos para tiqueteras de 80mm.
- **Facturación Inteligente:** Se diseñó el inventario incluyendo un flag `afecto_igv` a nivel de producto. De este modo, la Caja sabe exactamente qué cálculos tributarios enviar a Nubefact sin forzar a los administradores a conocer tecnicismos contables en su trabajo diario.

## 🏥 Módulos Principales
1. **Punto de Venta (Caja) y Facturación Inteligente**: Integración con Nubefact, cálculo de IGV, control de caja y visor de historial de facturación con reimpresión de tickets térmicos.
2. **Historias Clínicas y Triaje**: Registro detallado por consulta, subida de archivos (Radiografías) y emisión de recetas en PDF.
3. **Control de Vacunas**: Calendario de vacunación y control de dosis.
4. **Inventario Híbrido**: Control dual de "Productos" (con descuento de stock en caja) y "Servicios" (Consultas, Baños).
5. **Hospitalización (Internamiento)**: Panel de control visual de camas, bitácora de evolución médica (notas cronológicas) y altas médicas.
6. **Centro de Recordatorios**: Panel automatizado para gestionar las citas y vacunas pendientes del día y enviar notificaciones vía WhatsApp/SMS integrables con Twilio.
7. **Reportes y Analíticas**: Dashboard gerencial con Chart.js para medir ingresos, estado de atenciones y top de ventas en tiempo real.

## 🚀 Futuras Mejoras 
- Portal de auto-servicio para que los clientes vean las recetas de sus mascotas.
- Integración contable para multi-cajas simultáneas.
