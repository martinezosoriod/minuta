# Minuta Electrónica - Demo de Vigilancia

Sistema de gestión de vigilancia con trazabilidad completa, implementado en PHP 8.2+ con arquitectura MVC + Capa de Servicios.

## Arquitectura

- **Backend**: PHP 8.2+
- **Base de datos**: MySQL/MariaDB
- **Patrón**: MVC + Capa de Servicios
- **Acceso a datos**: PDO con Singleton

## Módulos Implementados

| Módulo | Prioridad | Estado |
|--------|-----------|--------|
| Registro de Ingreso | Alta | ✅ Completado |
| Entrega de Turno | Crítica | ✅ Completado |
| Novedades | Media | 📁 Estructura lista |
| Comunicados y Actas | Media | 📁 Estructura lista |

## Requisitos de Seguridad

- ✅ Trazabilidad obligatoria en toda transacción (`audit_logs`)
- ✅ Middleware para registrar usuario, timestamp y puesto_id
- ✅ Soft deletes para integridad de datos (prohibido borrado inconsistente)
- ✅ Integridad documental con hash SHA-256 (Ley 527)

## Instalación

### 1. Configurar base de datos

Editar `config/database.php` o usar variables de entorno:

```bash
export DB_HOST=localhost
export DB_DATABASE=minuta_electronica
export DB_USERNAME=root
export DB_PASSWORD=tu_password
```

### 2. Ejecutar esquema SQL

```bash
mysql -u root -p minuta_electronica < database/schema.sql
```

### 3. Configurar servidor web

Apuntar el document root a la carpeta `public/`

### 4. Verificar instalación

```bash
php public/index.php
```

## Endpoints API

### Registro de Ingresos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/access-logs` | Listar todos los registros |
| GET | `/api/access-logs/{id}` | Obtener registro específico |
| POST | `/api/access-logs` | Registrar nuevo ingreso |
| POST | `/api/access-logs/{id}/exit` | Registrar salida |
| GET | `/api/access-logs/active` | Visitantes activos |
| GET | `/api/posts/{id}/access-logs` | Registros por puesto |

### Entrega de Turno

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/posts/{id}/shifts` | Historial de turnos |
| GET | `/api/posts/{id}/shifts/active` | Turno activo |
| POST | `/api/posts/{id}/shifts/open` | Abrir turno |
| POST | `/api/shifts/{id}/close` | Cerrar turno |
| POST | `/api/shifts/{id}/handover` | Entrega de turno |
| POST | `/api/shifts/{id}/in-progress` | Marcar en progreso |

## Estructura del Proyecto

```
├── app/
│   ├── Controllers/      # Controladores MVC
│   ├── Models/           # Modelos de dominio
│   ├── Repositories/     # Capa de acceso a datos
│   └── Services/         # Lógica de negocio
├── config/               # Configuración
├── database/             # Esquemas SQL
├── logs/                 # Logs de aplicación
└── public/               # Punto de entrada web
```

## Características

- **Validación de turnos**: No se pueden registrar ingresos sin un turno activo
- **Entrega atómica de turnos**: Cierra anterior y abre nuevo en transacción
- **Auditoría completa**: Todas las operaciones quedan registradas
- **Soft deletes**: Los registros nunca se eliminan físicamente
- **Integridad documental**: Hash SHA-256 para actas y comunicados

## Licencia

MIT
