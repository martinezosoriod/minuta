<?php

namespace App\Services;

use PDO;
use App\Repositories\AccessLogRepository;
use App\Repositories\ShiftRepository;
use App\Repositories\AuditLogRepository;
use App\Models\AccessLog;

/**
 * Servicio para Registro de Ingresos (Prioridad Alta)
 * Implementa lógica de negocio y validación en backend
 */
class AccessLogService
{
    private AccessLogRepository $accessLogRepository;
    private ShiftRepository $shiftRepository;
    private AuditLogRepository $auditLogRepository;
    private ?int $currentUserId = null;
    private ?int $currentPostId = null;
    private ?string $ipAddress = null;
    private ?string $userAgent = null;

    public function __construct(
        AccessLogRepository $accessLogRepository,
        ShiftRepository $shiftRepository,
        AuditLogRepository $auditLogRepository
    ) {
        $this->accessLogRepository = $accessLogRepository;
        $this->shiftRepository = $shiftRepository;
        $this->auditLogRepository = $auditLogRepository;
    }

    /**
     * Configurar contexto del usuario actual para auditoría
     */
    public function setCurrentUser(int $userId, ?int $postId = null, ?string $ipAddress = null, ?string $userAgent = null): self
    {
        $this->currentUserId = $userId;
        $this->currentPostId = $postId;
        $this->ipAddress = $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? null;
        $this->userAgent = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? null;
        return $this;
    }

    /**
     * Registrar ingreso de visitante
     * Valida que exista un turno activo
     */
    public function registerAccess(array $data): AccessLog
    {
        // Validaciones requeridas
        if (empty($data['visitor_name'])) {
            throw new \InvalidArgumentException('El nombre del visitante es obligatorio');
        }

        if (empty($data['post_id'])) {
            throw new \InvalidArgumentException('El puesto es obligatorio');
        }

        if (empty($data['user_id'])) {
            throw new \InvalidArgumentException('El usuario que registra es obligatorio');
        }

        // Verificar que exista un turno activo para el puesto
        $activeShift = $this->shiftRepository->findActiveByPostId((int) $data['post_id']);
        
        if ($activeShift === null) {
            throw new \RuntimeException('No hay un turno activo para este puesto. Debe abrir un turno antes de registrar ingresos.');
        }

        // Crear registro de acceso
        $accessLog = new AccessLog();
        $accessLog->setPostId((int) $data['post_id'])
                  ->setUserId((int) $data['user_id'])
                  ->setShiftId($activeShift->getId())
                  ->setVisitorName($data['visitor_name'])
                  ->setVisitorDocument($data['visitor_document'] ?? null)
                  ->setVisitorCompany($data['visitor_company'] ?? null)
                  ->setPurpose($data['purpose'] ?? null)
                  ->setEntryTime(new \DateTime($data['entry_time'] ?? 'now'))
                  ->setVehiclePlate($data['vehicle_plate'] ?? null)
                  ->setPhotoPath($data['photo_path'] ?? null)
                  ->setStatus('inside');

        // Guardar en repositorio
        $savedAccessLog = $this->accessLogRepository->create($accessLog);

        // Registrar en auditoría (Requisito: Trazabilidad obligatoria)
        $this->auditLogRepository->logAction(
            action: 'CREATE',
            tableName: 'access_logs',
            userId: $this->currentUserId,
            postId: $this->currentPostId,
            recordId: $savedAccessLog->getId(),
            newValues: $savedAccessLog->toArray(),
            ipAddress: $this->ipAddress,
            userAgent: $this->userAgent
        );

        return $savedAccessLog;
    }

    /**
     * Registrar salida de visitante
     */
    public function registerExit(int $accessLogId): bool
    {
        $accessLog = $this->accessLogRepository->findById($accessLogId);
        
        if ($accessLog === null) {
            throw new \RuntimeException('Registro de acceso no encontrado');
        }

        if (!$accessLog->isInside()) {
            throw new \RuntimeException('El visitante ya ha salido previamente');
        }

        // Registrar salida
        $result = $this->accessLogRepository->registerExit($accessLogId, new \DateTime());

        if ($result) {
            // Actualizar modelo local
            $accessLog->setStatus('exited')
                      ->setExitTime(new \DateTime());

            // Registrar en auditoría
            $this->auditLogRepository->logAction(
                action: 'EXIT_REGISTERED',
                tableName: 'access_logs',
                userId: $this->currentUserId,
                postId: $this->currentPostId,
                recordId: $accessLogId,
                oldValues: ['status' => 'inside'],
                newValues: ['status' => 'exited', 'exit_time' => $accessLog->getExitTime()->format('Y-m-d H:i:s')],
                ipAddress: $this->ipAddress,
                userAgent: $this->userAgent
            );
        }

        return $result;
    }

    /**
     * Obtener todos los registros de acceso
     */
    public function getAllAccessLogs(): array
    {
        return $this->accessLogRepository->findAll();
    }

    /**
     * Obtener registro por ID
     */
    public function getAccessLogById(int $id): ?AccessLog
    {
        return $this->accessLogRepository->findById($id);
    }

    /**
     * Obtener visitantes activos actualmente dentro
     */
    public function getActiveVisitors(?int $postId = null): array
    {
        return $this->accessLogRepository->findActiveVisitors($postId);
    }

    /**
     * Obtener registros por puesto
     */
    public function getByPostId(int $postId): array
    {
        return $this->accessLogRepository->findByPostId($postId);
    }

    /**
     * Obtener registros por turno
     */
    public function getByShiftId(int $shiftId): array
    {
        return $this->accessLogRepository->findByShiftId($shiftId);
    }

    /**
     * Estadísticas de ingresos por puesto en rango de fechas
     */
    public function getAccessCountByPostAndDateRange(int $postId, string $startDate, string $endDate): int
    {
        return $this->accessLogRepository->countByPostAndDateRange($postId, $startDate, $endDate);
    }
}
