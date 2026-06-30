# 🧪 Simulación Completa — VetNeoLink

Recorrido paso a paso de todos los módulos del sistema.

---

## Acceso al Sistema

- **URL**: `http://localhost:8000`
- **Email**: `admin@vetnova.pe`
- **Contraseña**: `password`
- **Rol**: super_admin
- **Nombre del usuario**: Administrador VetNova

> El email del seeder es `admin@vetnova.pe` (creado en `database/seeders/ClinicaSeeder.php`).
> El placeholder del formulario muestra `admin@vetneolink.pe`, pero la cuenta real usa el email del seeder.

---

## 1. Login (`/`)

- Formulario con campos: Correo electrónico, Contraseña, checkbox "Recordarme".
- Botón "Ingresar" verde esmeralda.
- Header muestra "VetNeoLink" con icono de corazón y subtítulo "Sistema de Gestión Veterinaria".
- Footer: "© 2026 VetNeoLink · Hecho en Perú 🇵🇪".

---

## 2. Dashboard (`/dashboard`)

- **Título de pestaña**: "Dashboard — VetNeoLink"
- **Tema**: Dark mode (fondo gris oscuro, acentos esmeralda, texto blanco).
- **Saludo**: "¡Hola, Administrador VetNova! Hoy es martes 30 de junio. Tienes 0 citas por atender."
- **Tarjetas resumen**:
  - Ingresos de Hoy: S/ 0.00
  - Citas para Hoy: 0
  - Nuevos Pacientes (Mes): 0
  - Alertas de Inventario: 0 (Stock saludable)
- **Próximas Citas (Hoy)**: "No tienes citas pendientes para el resto del día."
- **Ventas Recientes**: Lista transacciones con cliente, fecha/hora, tipo de comprobante (TICKET/BOLETA), monto total y método de pago (YAPE_PLIN, TRANSFERENCIA, EFECTIVO).
- **Sidebar**: Navegación completa con secciones Clínica y Operaciones desplegables, info del usuario logueado al fondo, botón de cerrar sesión.

---

## 3. Agenda de Citas (`/citas`)

- **Título**: "Agenda de Citas — VetNeoLink"
- **Botón principal**: "Agendar Cita"
- **Filtros**: Fecha (date picker), Estado (dropdown "Todos los estados"), Búsqueda rápida (texto).
- **Badges de estado**: Por Atender (0), En Progreso (0), Completadas (0), No Asistieron (0).
- **Tabla**: Columnas Horario, Paciente, Motivo y Vet., Estado.
- **Estado vacío**: "No hay citas programadas para estos filtros."

---

## 4. Clientes (`/clientes`)

- **Título**: "Clientes — VetNeoLink"
- **Botón principal**: "Nuevo Cliente"
- **Buscador**: "Buscar por nombre o DNI/RUC..."
- **Tabla**: Columnas Documento, Cliente, Contacto, Estado, Acciones.
- **Datos encontrados**:
  - RUC 20491363402 — Universidad Nacional De Cañete (Contamana)
  - DNI 60323413 — Victor Geovani Sandoval Rosales (Imperial)
  - DNI 73126518 — Jorge Enrique Caceres Hernandez (San Vicente De Cañete)
  - DNI 74701279 — Naomi Shantall Cama Elias (Imperial)
- **Acciones por fila**: Editar (lápiz azul), Eliminar (basura rojo).

---

## 5. Mascotas (`/mascotas`)

- **Título**: "Mascotas — VetNeoLink"
- **Botón principal**: "Nueva Mascota"
- **Filtros**: Especie (dropdown), Sexo (dropdown), Búsqueda por mascota/dueño/DNI.
- **Tabla**: Columnas Mascota, Detalles, Propietario, Estado, Acciones.
- **Datos encontrados**:
  - Jech (Perro, Doberman, Macho, 1.20 kg)
  - Clara (Gato, Siamés, Hembra)
  - Cuto (Perro, Pastor Alemán Cruzado, Macho, 10.00 kg, 14 años)
  - Nao (Perro, Macho, 11.99 kg)
- **Acciones por fila**: Ver Historia Clínica, Editar, Desactivar/Archivar.

---

## 6. Historias Clínicas (`/historias`)

- **Título**: "Historias Clínicas — VetNeoLink"
- **Botón principal**: "Nueva Consulta"
- **Filtros**: Fecha de consulta (date picker), Búsqueda por paciente/dueño/motivo.
- **Tabla**: Columnas Fecha, Paciente, Motivo / Triage, Atendió, Acciones.
- **Datos encontrados**:
  - 28 abr. 2026, 07:01 AM — Cuto — Motivo: "Bichos" — Triage: 10.00 kg, 35.0°C — Atendió: Administrador VetNova.
- **Acciones por fila**: Ver detalle completo (ojo), Editar (lápiz).

---

## 7. Vacunas y Desparasitaciones (`/vacunas`)

- **Título**: "Vacunas y Desparasitaciones — VetNeoLink"
- **Botón principal**: "Nuevo Registro"
- **Filtros**: Tipo (dropdown "Todos los tipos"), Búsqueda por mascota/producto.
- **Tabla**: Columnas Aplicación, Paciente, Producto / Detalle, Próxima Dosis.
- **Estado vacío**: "No hay registros preventivos aún."

---

## 8. Catálogo e Inventario (`/inventario`)

- **Título**: "Catálogo e Inventario — VetNeoLink"
- **Botón principal**: "Nuevo Item"
- **Filtros**: Tipo (dropdown), Toggle "Sólo Stock Bajo", Búsqueda por nombre/código.
- **Tabla**: Columnas Item / Servicio, Precio, Stock, Estado, Acciones.
- **Datos encontrados**:
  - Vacuna Antirrabia — S/ 55.00 — Stock: 10 — Activo
- **Acciones por fila**: Ajustar Stock, Editar, Eliminar.

---

## 9. Punto de Venta / Caja (`/caja`)

- **Título**: "Caja y Facturación — VetNeoLink"
- **Panel izquierdo (Venta Actual)**:
  - Buscador: "Agregar Producto o Servicio" (dropdown con búsqueda).
  - Tabla: Item, Cant., Precio Un., Total.
  - Estado vacío: "Aún no hay productos en la venta actual."
- **Panel derecho (Detalle del Cobro)**:
  - 1. Cliente: Dropdown "Seleccionar cliente (Obligatorio)".
  - 2. Comprobante y Pago: Método de pago (Efectivo por defecto). Tipos: NOTA_VENTA, BOLETA, FACTURA, TICKET.
  - 3. Resumen: Subtotal, IGV (18%), TOTAL A PAGAR (S/ 0.00).
  - Botón: "Agregue productos para cobrar" (deshabilitado sin productos).

---

## 10. Facturación e Historial (`/facturacion`)

- **Título**: "Facturación e Historial — VetNeoLink"
- **Botón**: "Ir a Caja"
- **Filtros**: Fecha (date picker), Búsqueda por cliente/DNI.
- **Tabla**: Columnas Nro Comprobante, Cliente, Documento, Total, Pago, Estado.

---

## 11. Hospitalización (`/hospitalizacion`)

- **Título**: "Hospitalización — VetNeoLink"
- **Botón principal**: "Nuevo Ingreso"
- **Estado vacío**: "Sin pacientes internados — No hay ninguna mascota ocupando jaulas en este momento."

---

## 12. Centro de Recordatorios (`/recordatorios`)

- **Título**: "Centro de Recordatorios — VetNeoLink"
- **Botón**: Refrescar.
- **Secciones**:
  - Recordatorios de Citas (Hoy y Mañana): "No hay citas programadas para notificar."
  - Control Preventivo (Vencimientos Próximos): "No hay vacunas pendientes para notificar."

---

## 13. Reportes y Analíticas (`/reportes`)

- **Título**: "Reportes y Analíticas — VetNeoLink"
- **Tarjetas resumen**:
  - Ingresos Junio: S/ 0.00
  - Ticket Promedio: S/ 0.00
  - Ventas Realizadas: 0
  - Pacientes Nuevos: 0
- **Gráficos**:
  - Ingresos Semanales (S/) — gráfico de barras/líneas.
  - Estado de Citas (30d) — gráfico circular/donut.

---

## 14. Configuración (`/configuracion`)

- **Título**: "Configuración — VetNeoLink"
- **Panel izquierdo (Datos Generales)**: Formulario con Nombre comercial, RUC, Razón Social, Dirección fiscal, Teléfono, Email, Sitio web. Botón "Guardar Cambios".
- **Panel derecho (Sistema)**: VetNeoLink v1.0.0, Laravel 12.57.0, PHP 8.2.12.
- **Integraciones**:
  - PeruAPI (DNI/RUC): Configurada ✅
  - Nubefact (SUNAT): Sin configurar ⚠️
  - Twilio (WhatsApp): Configurada ✅
- **Sucursales**: Lista "Sede Principal" con dirección y teléfono. Botón "Nueva Sucursal".

---

## 15. Gestión de Usuarios (`/usuarios`)

- **Título**: "Gestión de Usuarios — VetNeoLink"
- **Botón principal**: "Nuevo Usuario"
- **Buscador**: "Buscar por nombre, email o DNI..."
- **Tabla**: Columnas Nombre, Email, Rol, Estado, Acciones.
- **Usuarios registrados**:
  - Administrador VetNova — admin@vetnova.pe — super_admin — Activo
  - Jorge Lucio Caceres Vargas — DNI 15351607 — vet1@vetnova.pe — 986621345 — Activo
- **Acciones**: Editar, Desactivar/Bloquear (excepto super_admin).

---

## Navegación del Sidebar

```
Dashboard
Citas (badge "Nuevo")
─── Separador ───
Clínica (desplegable)
  ├── Clientes
  ├── Mascotas
  ├── Historia Clínica
  └── Vacunas
Operaciones (desplegable)
  ├── Inventario
  ├── Punto de Venta (badge "POS")
  ├── Facturación
  └── Hospitalización
─── Separador ───
Recordatorios
Reportes
─── Separador ───
Configuración
Usuarios
```

**Usuario logueado**: Mostrado al fondo del sidebar con avatar (inicial del nombre), nombre y rol.
**Cerrar sesión**: Botón con icono de flecha en la navbar superior.
