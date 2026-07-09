<?php

namespace App\Services;

use PDO;
use App\Repositories\ShiftRepository;
use App\Repositories\AuditLogRepository;
use App\Models\Shift;

/**
 * Servicio para Entrega de Turno (Prioridad Crítica)
 * Desarrollar lógica de cierre y apertura de turno asegurando continuidad
 * Validar cierre del turno anterior
 */
class ShiftService
{
    private ShiftRepository $shiftRepository;
    private AuditLogRepository $auditLogRepository;
    private ?int $currentUserId = null;
    private ?int $currentPostId = null;
    private ?string $ipAddress = null;
    private ?string $userAgent = null;

    public function __construct(
        ShiftRepository $shiftRepository,
        AuditLogRepository $auditLogRepository
    ) {
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
     * Abrir nuevo turno
     * Valida que no exista un turno activo previo (Requisito crítico)
     */
    public function openShift(int $postId, int $userId, ?string $observations = null): Shift
    {
        // Verificar si ya existe un turno activo
        if ($this->shiftRepository->hasOpenShift($postId)) {
            throw new \RuntimeException(
                'No se puede abrir un nuevo turno: Ya existe un turno activo para este puesto. ' .
                'Debe cerrar el turno anterior primero para asegurar la continuidad.'
            );
        }

        // Abrir turno
        $shift = $this->shiftRepository->openShift($postId, $userId, $observations);

        // Registrar en auditoría
        $this->auditLogRepository->logAction(
            action: 'SHIFT_OPENED',
            tableName: 'shifts',
            userId: $this->currentUserId,
            postId: $postId,
            recordId: $shift->getId(),
            newValues: [
                'post_id' => $postId,
                'user_id' => $userId,
                'start_time' => $shift->getStartTime()->format('Y-m-d H:i:s'),
                'status' => 'open'
            ],
            ipAddress: $this->ipAddress,
            userAgent: $this->userAgent
        );

        return $shift;
    }

    /**
     * Cerrar turno
     * Asegura continuidad registrando el tiempo de fin y observaciones
     */
    public function closeShift(int $shiftId, ?string $observations = null): bool
    {
        $shift = $this->shiftRepository->findById($shiftId);

        if ($shift === null) {
            throw new \RuntimeException('Turno no encontrado');
        }

        if ($shift->isClosed()) {
            throw new \RuntimeException('El turno ya está cerrado');
        }

        // Obtener datos anteriores para auditoría
        $oldValues = [
            'status' => $shift->getStatus(),
            'end_time' => null
        ];

        // Cerrar turno
        $result = $this->shiftRepository->closeShift($shiftId, $observations);

        if ($result) {
            // Registrar en auditoría
            $this->auditLogRepository->logAction(
                action: 'SHIFT_CLOSED',
                tableName: 'shifts',
                userId: $this->currentUserId,
                postId: $shift->getPostId(),
                recordId: $shiftId,
                oldValues: $oldValues,
                newValues: [
                    'status' => 'closed',
                    'end_time' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'observations' => $observations
                ],
                ipAddress: $this->ipAddress,
                userAgent: $this->userAgent
            );
        }

        return $result;
    }

    /**
     * Realizar entrega de turno (cierre del anterior + apertura del nuevo)
     * Operación atómica que asegura continuidad
     */
    public function handoverShift(
        int $previousShiftId,
        int $newUserId,
        string $handoverNotes,
        ?string $newObservations = null
    ): Shift {
        $pdo = $this->shiftRepository instanceof \App\Repositories\BaseRepository 
            ? (fn() => $this->db ?? null)->call($this->shiftRepository) 
            : null;

        // Iniciar transacción si hay acceso a PDO
        if ($pdo !== null) {
            $pdo->beginTransaction();
        }

        try {
            // 1. Cerrar turno anterior con notas de entrega
            $previousShift = $this->shiftRepository->findById($previousShiftId);
            
            if ($previousShift === null) {
                throw new \RuntimeException('Turno anterior no encontrado');
            }

            if (!$previousShift->isOpen()) {
                throw new \RuntimeException('El turno anterior debe estar abierto para realizar la entrega');
            }

            // Cerrar turno anterior con las notas de entrega
            $notes = "ENTREGA DE TURNO: {$handoverNotes}";
            $this->shiftRepository->closeShift($previousShiftId, $notes);

            // 2. Abrir nuevo turno inmediatamente
            $newShift = $this->shiftRepository->openShift(
                $previousShift->getPostId(),
                $newUserId,
                $newObservations ?? "Continuación del turno anterior. Notas: {$handoverNotes}"
            );

            // Registrar auditoría de la entrega completa
            $this->auditLogRepository->logAction(
                action: 'SHIFT_HANDOVER',
                tableName: 'shifts',
                userId: $this->currentUserId,
                postId: $previousShift->getPostId(),
                recordId: $newShift->getId(),
                oldValues: ['previous_shift_id' => $previousShiftId],
                newValues: [
                    'new_shift_id' => $newShift->getId(),
                    'new_user_id' => $newUserId,
                    'handover_notes' => $handoverNotes
                ],
                ipAddress: $this->ipAddress,
                userAgent: $this->userAgent
            );

            if ($pdo !== null) {
                $pdo->commit();
            }

            return $newShift;

        } catch (\Throwable $e) {
            if ($pdo !== null && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Obtener turno activo por puesto
     */
    public function getActiveShiftByPostId(int $postId): ?Shift
    {
        return $this->shiftRepository->findActiveByPostId($postId);
    }

    /**
     * Obtener turno activo por usuario
     */
    public function getActiveShiftByUserId(int $userId): ?Shift
    {
        return $this->shiftRepository->findActiveByUserId($userId);
    }

    /**
     * Verificar si hay turno activo en un puesto
     */
    public function hasActiveShift(int $postId): bool
    {
        return $this->shiftRepository->hasOpenShift($postId);
    }

    /**
     * Obtener historial de turnos por puesto
     */
    public function getShiftHistory(int $postId, int $limit = 50): array
    {
        return $this->shiftRepository->findByPostId($postId, $limit);
    }

    /**
     * Marcar turno como en progreso
     */
    public function markShiftAsInProgress(int $shiftId): bool
    {
        $result = $this->shiftRepository->markAsInProgress($shiftId);

        if ($result) {
            $this->auditLogRepository->logAction(
                action: 'SHIFT_IN_PROGRESS',
                tableName: 'shifts',
                userId: $this->currentUserId,
                postId: null,
                recordId: $shiftId,
                newValues: ['status' => 'in_progress'],
                ipAddress: $this->ipAddress,
                userAgent: $this->userAgent
            );
        }

        return $result;
    }
}
