<?php

namespace App\Repositories;

use PDO;
use App\Models\Shift;
use App\Models\BaseModel;

/**
 * Repositorio para Shift - Turnos de trabajo (Prioridad Crítica)
 * Desarrollar lógica de cierre y apertura de turno asegurando continuidad
 */
class ShiftRepository extends BaseRepository
{
    protected string $table = 'shifts';
    protected string $modelClass = Shift::class;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Obtener turno activo por puesto
     */
    public function findActiveByPostId(int $postId): ?Shift
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE post_id = :post_id AND status IN ('open', 'in_progress') AND deleted_at IS NULL 
                ORDER BY start_time DESC 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        
        $result = $stmt->fetchObject($this->modelClass);
        return $result ?: null;
    }

    /**
     * Obtener turno activo por usuario
     */
    public function findActiveByUserId(int $userId): ?Shift
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id AND status IN ('open', 'in_progress') AND deleted_at IS NULL 
                ORDER BY start_time DESC 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        $result = $stmt->fetchObject($this->modelClass);
        return $result ?: null;
    }

    /**
     * Verificar si existe un turno abierto para un puesto
     * Requisito: Validar cierre del turno anterior antes de abrir uno nuevo
     */
    public function hasOpenShift(int $postId): bool
    {
        return $this->findActiveByPostId($postId) !== null;
    }

    /**
     * Abrir nuevo turno
     * Valida que no exista un turno activo previo
     */
    public function openShift(int $postId, int $userId, ?string $observations = null): ?Shift
    {
        // Validar que no haya turno activo
        if ($this->hasOpenShift($postId)) {
            throw new \RuntimeException('Ya existe un turno activo para este puesto. Debe cerrar el turno anterior primero.');
        }

        $shift = new Shift();
        $shift->setPostId($postId)
              ->setUserId($userId)
              ->setStartTime(new \DateTime())
              ->setStatus('open')
              ->setObservations($observations);

        return $this->create($shift);
    }

    /**
     * Cerrar turno
     * Asegura continuidad registrando el tiempo de fin
     */
    public function closeShift(int $shiftId, ?string $observations = null): bool
    {
        $sql = "UPDATE {$this->table} 
                SET end_time = CURRENT_TIMESTAMP, 
                    status = 'closed', 
                    observations = COALESCE(:observations, observations),
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id AND deleted_at IS NULL AND status IN ('open', 'in_progress')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $shiftId,
            'observations' => $observations
        ]);
    }

    /**
     * Cambiar estado del turno a 'in_progress'
     */
    public function markAsInProgress(int $shiftId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'in_progress', updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id AND deleted_at IS NULL AND status = 'open'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $shiftId]);
    }

    /**
     * Obtener historial de turnos por puesto
     */
    public function findByPostId(int $postId, int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE post_id = :post_id AND deleted_at IS NULL 
                ORDER BY start_time DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('post_id', $postId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
    }

    /**
     * Obtener turnos por usuario
     */
    public function findByUserId(int $userId, int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id AND deleted_at IS NULL 
                ORDER BY start_time DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
    }

    /**
     * Contar turnos cerrados en un rango de fechas
     */
    public function countClosedByDateRange(int $postId, string $startDate, string $endDate): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE post_id = :post_id 
                AND status = 'closed'
                AND end_time BETWEEN :start_date AND :end_date 
                AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'post_id' => $postId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    protected function extractData(BaseModel $model): array
    {
        /** @var Shift $model */
        return [
            'post_id' => $model->getPostId(),
            'user_id' => $model->getUserId(),
            'start_time' => $model->getStartTime()?->format('Y-m-d H:i:s'),
            'end_time' => $model->getEndTime()?->format('Y-m-d H:i:s'),
            'status' => $model->getStatus() ?? 'open',
            'observations' => $model->getObservations()
        ];
    }
}
