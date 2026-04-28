# 🐾 VetNova — Sistema Integral de Gestión Veterinaria

VetNova es una plataforma SaaS de alto rendimiento diseñada específicamente para modernizar y optimizar la operación de clínicas veterinarias. No es solo un software de administración; es un ecosistema técnico que garantiza la trazabilidad médica, la eficiencia operativa y una experiencia premium para el cliente final.

## 🎯 Objetivo del Proyecto

El objetivo de VetNova es centralizar todos los procesos críticos de una clínica veterinaria —desde la agenda médica hasta la facturación electrónica y el control estricto de inventario— bajo una interfaz moderna, rápida y segura que minimice el error humano y maximice la rentabilidad.

## 🚀 Problemas que Resuelve

1.  **Pérdida de Trazabilidad Médica:** Historiales clínicos desordenados o ilegibles. VetNova ofrece un sistema de Historias Clínicas estructurado con motor de impresión profesional.
2.  **Descontrol de Inventario:** Pérdida de dinero por productos vencidos o stock no registrado. Nuestro **Kardex Profesional** rastrea lotes, vencimientos y costos históricos.
3.  **Ausentismo en Citas:** Notificaciones automáticas vía WhatsApp, SMS y Email para reducir las inasistencias.
4.  **Complejidad Administrativa:** Integración con **PeruAPI** para búsqueda instantánea de DNI/RUC y cumplimiento con **Nubefact** para facturación electrónica.

## 🛠️ Tecnologías Utilizadas

*   **Backend:** PHP 8.2+ con **Laravel 11** (Robustez y Escalabilidad).
*   **Frontend Dinámico:** **Livewire 3** (Reactividad sin salir de PHP) + **Alpine.js**.
*   **Diseño / UI:** **Tailwind CSS** + **Mary UI** (DaisyUI) para una estética Premium Dark Mode.
*   **Base de Datos:** **MySQL / MariaDB** (Estándar de industria).
*   **Integraciones:**
    *   **PeruAPI:** Validación de identidad y tributaria.
    *   **Twilio / Resend:** Comunicaciones omnicanal.
    *   **Nubefact:** Facturación electrónica SUNAT.

## 🏗️ Arquitectura del Sistema

VetNova sigue una arquitectura modular y desacoplada, priorizando la mantenibilidad:

```text
/app
  /Livewire      -> Componentes reactivos por módulo (Caja, Citas, Historias, etc.)
  /Models        -> Entidades de negocio con Eloquent.
  /Services      -> Lógica de integración externa (PeruAPI, Notificaciones).
  /Traits        -> Funcionalidades compartidas (Alertas, Modales).
/database
  /migrations    -> Estructura de tablas y relaciones PK/FK.
  /seeders       -> Datos iniciales para roles y configuración base.
/resources
  /views         -> Vistas Blade optimizadas y componentes UI.
/public          -> Assets y motor de impresión Iframe.
```

## 📋 Detalle de Módulos Críticos

### 🩺 Gestión Clínica (Historias)
*   **Contexto Inteligente:** Al iniciar una atención desde la agenda, el sistema arrastra automáticamente los datos del paciente y motivo.
*   **Motor de Impresión:** Utiliza un sistema encapsulado en Iframe para garantizar que el PDF generado sea limpio, de alto contraste y profesional, independientemente del navegador.

### 📦 Inventario & Kardex Profesional
*   **Trazabilidad Total:** Registro obligatorio de Lote y Fecha de Vencimiento para biológicos y fármacos críticos.
*   **Costeo Histórico:** A diferencia de sistemas básicos, VetNova registra el costo unitario de cada transacción, permitiendo auditorías financieras precisas.
*   **Alertas de Reposición:** Sistema de semaforización basado en `stock_minimo`.

### 📅 Agenda y Notificaciones
*   **Flujo de Estados:** Pendiente → En Progreso → Completada (Vinculada a Historia Clínica).
*   **Omnicanalidad:** Notificaciones automáticas al cliente tras la reserva para asegurar la asistencia.

### 💳 Caja y Finanzas
*   **Integración de Catálogo:** Venta directa de productos y servicios con actualización de stock en tiempo real.
*   **Facturación Electrónica:** Preparado para envío automático a SUNAT vía Nubefact.

## ⚙️ Instalación y Configuración

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/usuario/vetnova.git
    cd vetnova
    ```
2.  **Instalar dependencias:**
    ```bash
    composer install
    npm install
    ```
3.  **Configurar entorno:**
    *   Copiar `.env.example` a `.env`.
    *   Configurar credenciales de Base de Datos y las llaves de **PeruAPI**.
4.  **Ejecutar Migraciones:**
    ```bash
    php artisan migrate --seed
    ```

## 🚀 Despliegue (Railway)

VetNova está optimizado para desplegarse en **Railway** en cuestión de minutos:

1.  El proyecto incluye los archivos `nixpacks.toml` y `railway.toml` para configuración automática.
2.  Conecta tu repositorio de GitHub a un nuevo proyecto en Railway.
3.  Agrega una base de datos MySQL en el mismo proyecto.
4.  Configura las variables de entorno (`APP_KEY`, `PERUAPI_KEY`, etc.).
5.  **Consulta la [Guía Detallada de Deploy](DEPLOY_GUIDE.md)** para configuraciones de IP fija y PeruAPI.

## 💡 Decisiones Técnicas Clave

*   **Uso de Livewire 3:** Se eligió para mantener toda la lógica de negocio en PHP, facilitando el mantenimiento por un solo equipo y garantizando una reactividad tipo SPA.
*   **Inmutabilidad del Kardex:** Los registros de inventario nunca se editan ni eliminan; cada ajuste genera un nuevo movimiento para garantizar la integridad en auditorías.
*   **Seguridad RBAC:** Implementación de roles y permisos granulares para proteger datos sensibles de clientes y auditoría de movimientos.

## 🔮 Futuras Mejoras

*   **App Móvil PWA:** Para que los veterinarios puedan registrar datos desde tablets en consultorio.
*   **Módulo de Laboratorio:** Integración con máquinas de análisis de sangre para carga automática de resultados.
*   **Inteligencia Artificial:** Análisis predictivo de re-stock basado en la velocidad de consumo de insumos.

---
**Desarrollado con ❤️ para el sector veterinario moderno.**
