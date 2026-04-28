# VetNova — Ecosistema Digital de Gestión Veterinaria Avanzada (SaaS)

> **Documentación Técnica y Memoria Descriptiva de Innovación Tecnológica**  
> *Preparado para especificaciones de propiedad intelectual y registros de utilidad.*

## 1. Resumen Ejecutivo
**VetNova** es una plataforma de software distribuida (SaaS) diseñada bajo una arquitectura multi-inquilino (Multi-tenant) que automatiza y centraliza la operatividad clínica, comercial y logística de establecimientos veterinarios. El sistema integra procesos de triaje clínico, facturación electrónica legal (SUNAT), gestión de inventario inmutable (Kardex) y un motor de notificaciones omnicanal asíncrono.

## 2. Descripción del Problema y Solución Técnica
### 2.1 Problema Identificado
La gestión veterinaria tradicional adolece de fragmentación de datos: las historias clínicas no se comunican con el inventario, y la comunicación con el cliente depende de procesos manuales propensos al error humano.

### 2.2 Solución Innovadora (Métrica de Utilidad)
VetNova implementa un **Motor de Sincronización Clínica-Comercial**. Cuando un médico prescribe un fármaco en la Historia Clínica, el sistema reserva automáticamente el stock en el inventario y genera una pre-orden en el Punto de Venta (POS), eliminando la duplicidad de tareas y garantizando que ningún tratamiento quede sin facturar o sin registro logístico.

## 3. Especificaciones Técnicas y Stack
- **Núcleo de Procesamiento:** PHP 8.3 / Laravel 11 (Framework de alta concurrencia).
- **Capa de Reactividad:** Livewire 3 + Alpine.js (SPA feel sin sobrecarga de JS).
- **Sistema de Diseño:** Tailwind CSS v4 + Mary UI (Aesthetics "Pro Max" premium).
- **Base de Datos:** MySQL 8 con motor InnoDB y soporte para transacciones ACID.
- **Motor de Notificaciones:** Integración con Twilio (WhatsApp/SMS) y Resend (Email SMTP dinámico).
- **Integraciones Externas:**
  - **PeruAPI:** Protocolo de consulta RUC/DNI en milisegundos.
  - **Nubefact:** Pasarela de Facturación Electrónica compatible con estándares SUNAT.

## 4. Arquitectura del Sistema
### 4.1 Multi-tenancy Lógico
Aislamiento estricto de datos a nivel de capa de consulta. Cada tabla crítica (`ventas`, `citas`, `pacientes`) incluye un índice compuesto por `clinica_id`, garantizando que la información de una clínica sea invisible para otra, incluso en la misma base de datos física.

### 4.2 Patrón Service Layer
Implementación de servicios desacoplados en `app/Services`:
- **NotificationService:** Gestiona la lógica de formateo E.164 para telefonía y plantillas HTML para correos.
- **KardexService:** Algoritmo de cálculo de saldos basado en el historial inmutable de movimientos.

## 5. Módulos Funcionales Detallados

### 5.1 Gestión Clínica y Expediente Digital
- **Historia Clínica Orientada a Problemas (HCOP):** Registro cronológico de consultas, triaje (peso, temperatura, frecuencia cardíaca), diagnóstico y planes de tratamiento.
- **Hospitalización (Internamiento):** Panel de control de ocupación de caniles con bitácora de evolución médica minuto a minuto.

### 5.2 Motor de Notificaciones Omnicanal
- **Automatización de Recordatorios:** Algoritmo que identifica citas y refuerzos de vacunas próximos (ventana de 48h).
- **Omnicanalidad Real:** Permite el envío de recordatorios vía WhatsApp, SMS tradicional y Correo Electrónico con un solo clic, utilizando datos sincronizados en tiempo real del cliente.

### 5.3 Control Logístico (Kardex Inmutable)
- **Gestión de Stock:** Diferenciación técnica entre productos físicos (medicinas, alimentos) y servicios (baños, consultas).
- **Trazabilidad Total:** Registro inmutable de cada entrada (compra) y salida (venta/ajuste), permitiendo auditorías de inventario retroactivas.

### 5.4 Punto de Venta (POS) y Facturación
- **Caja Rápida:** Buscador asíncrono de productos y clientes.
- **Validación SUNAT:** Emisión de Boletas y Facturas electrónicas mediante integración REST API.
- **Cierre de Caja:** Reporte detallado de ingresos por método de pago (Efectivo, Yape/Plin, Tarjetas).

## 6. Seguridad y Permisos
- **RBAC (Role-Based Access Control):** Implementado vía Spatie, permitiendo roles como `super_admin`, `veterinario`, `recepcionista` y `vendedor`, con permisos granulares por módulo.
- **Validación de Datos:** Uso intensivo de Form Requests y validaciones en tiempo real de Livewire para prevenir inyecciones y datos corruptos.

## 7. Instalación y Despliegue
1. **Dependencias:** `composer install` & `npm install`.
2. **Entorno:** Configurar `.env` con credenciales de DB y API Keys de Twilio/Resend.
3. **Persistencia:** `php artisan migrate --seed` (Incluye datos base para pruebas).
4. **Build:** `npm run build` para optimizar activos en producción.

## 8. Visión de Escalabilidad
El sistema está preparado para la implementación de:
- **Telemedicina:** Módulo de videoconsultas integrado.
- **Analítica Predictiva:** Uso de IA para predecir picos de demanda en vacunas estacionales.
- **APP Cliente:** Aplicación móvil para que los dueños de mascotas consulten sus registros médicos.

---
**VetNova** no es solo un software, es la columna vertebral tecnológica para la modernización del sector veterinario.
