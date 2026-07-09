<?php

/**
 * Minuta Electrónica - Demo de Vigilancia
 * 
 * Arquitectura: MVC + Capa de Servicios
 * Backend: PHP 8.2+
 * Base de datos: MySQL/MariaDB
 * Acceso a datos: PDO
 * 
 * Módulos implementados:
 * - Registro de Ingreso (Prioridad Alta)
 * - Novedades (Estructura lista)
 * - Entrega de Turno (Prioridad Crítica)
 * - Comunicados y Actas (Estructura lista)
 * 
 * Requisitos de seguridad:
 * - Trazabilidad obligatoria en toda transacción (audit_logs)
 * - Middleware para registrar usuario, timestamp y puesto_id
 * - Soft deletes para integridad de datos
 */

// Autoloader PSR-4
require_once __DIR__ . '/autoload.php';

use App\Services\Database;
use App\Services\AccessLogService;
use App\Services\ShiftService;
use App\Repositories\AccessLogRepository;
use App\Repositories\ShiftRepository;
use App\Repositories\AuditLogRepository;

// Ejemplo de uso básico
echo "=== Minuta Electrónica - Demo de Vigilancia ===\n\n";

try {
    // Obtener instancia de base de datos
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "✓ Conexión a base de datos establecida\n";
    
    // Inicializar repositorios
    $accessLogRepo = new AccessLogRepository($pdo);
    $shiftRepo = new ShiftRepository($pdo);
    $auditLogRepo = new AuditLogRepository($pdo);
    
    echo "✓ Repositorios inicializados\n";
    
    // Inicializar servicios
    $accessLogService = new AccessLogService($accessLogRepo, $shiftRepo, $auditLogRepo);
    $shiftService = new ShiftService($shiftRepo, $auditLogRepo);
    
    echo "✓ Servicios inicializados\n\n";
    
    echo "=== Estructura del proyecto ===\n\n";
    echo "app/\n";
    echo "├── Controllers/\n";
    echo "│   ├── BaseController.php\n";
    echo "│   ├── AccessLogController.php\n";
    echo "│   └── ShiftController.php\n";
    echo "├── Models/\n";
    echo "│   ├── BaseModel.php\n";
    echo "│   ├── User.php\n";
    echo "│   ├── Post.php\n";
    echo "│   ├── Shift.php\n";
    echo "│   ├── AccessLog.php\n";
    echo "│   ├── Incident.php\n";
    echo "│   ├── Document.php\n";
    echo "│   └── AuditLog.php\n";
    echo "├── Repositories/\n";
    echo "│   ├── BaseRepository.php\n";
    echo "│   ├── AccessLogRepository.php\n";
    echo "│   ├── ShiftRepository.php\n";
    echo "│   └── AuditLogRepository.php\n";
    echo "└── Services/\n";
    echo "    ├── Database.php\n";
    echo "    ├── AccessLogService.php\n";
    echo "    └── ShiftService.php\n\n";
    
    echo "database/\n";
    echo "└── schema.sql\n\n";
    
    echo "config/\n";
    echo "└── database.php\n\n";
    
    echo "=== Endpoints disponibles ===\n\n";
    echo "Registro de Ingresos:\n";
    echo "  GET    /api/access-logs              - Listar todos los registros\n";
    echo "  GET    /api/access-logs/{id}         - Obtener registro específico\n";
    echo "  POST   /api/access-logs              - Registrar nuevo ingreso\n";
    echo "  POST   /api/access-logs/{id}/exit    - Registrar salida\n";
    echo "  GET    /api/access-logs/active       - Visitantes activos\n";
    echo "  GET    /api/posts/{id}/access-logs   - Registros por puesto\n";
    echo "\n";
    echo "Entrega de Turno:\n";
    echo "  GET    /api/posts/{id}/shifts        - Historial de turnos\n";
    echo "  GET    /api/posts/{id}/shifts/active - Turno activo\n";
    echo "  POST   /api/posts/{id}/shifts/open   - Abrir turno\n";
    echo "  POST   /api/shifts/{id}/close        - Cerrar turno\n";
    echo "  POST   /api/shifts/{id}/handover     - Entrega de turno\n";
    echo "  POST   /api/shifts/{id}/in-progress  - Marcar en progreso\n";
    echo "\n";
    
    echo "=== Características implementadas ===\n\n";
    echo "✓ Patrón MVC + Capa de Servicios\n";
    echo "✓ Repositorios con acceso a datos via PDO\n";
    echo "✓ Modelos con soporte para soft deletes\n";
    echo "✓ Auditoría obligatoria en todas las transacciones\n";
    echo "✓ Validación de turnos activos antes de registrar ingresos\n";
    echo "✓ Entrega de turno atómica (cierre + apertura)\n";
    echo "✓ Integridad documental con hash SHA-256 (Ley 527)\n";
    echo "✓ Conexión segura con Singleton Pattern\n";
    echo "✓ Configuración vía archivo o variables de entorno\n";
    echo "\n";
    
    echo "=== Para comenzar ===\n\n";
    echo "1. Configurar base de datos en config/database.php\n";
    echo "2. Ejecutar database/schema.sql en MySQL/MariaDB\n";
    echo "3. Configurar servidor web para apuntar a public/\n";
    echo "4. Acceder a los endpoints API\n";
    echo "\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
