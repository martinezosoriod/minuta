<?php

namespace App\Repositories;

use PDO;
use App\Models\AccessLog;
use App\Models\BaseModel;

/**
 * Repositorio para AccessLog - Registro de Ingresos (Prioridad Alta)
 * Punto de entrada para el demo funcional
 */
class AccessLogRepository extends BaseRepository
{
    protected string $table = 'access_logs';
    protected string $modelClass = AccessLog::class;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Buscar registros por puesto
     */
    public function findByPostId(int $postId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE post_id = :post_id AND deleted_at IS NULL 
                ORDER BY entry_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['post_id' => $postId]);
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
    }

    /**
     * Buscar registros por usuario
     */
    public function findByUserId(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id AND deleted_at IS NULL 
                ORDER BY entry_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
    }

    /**
     * Buscar registros por turno
     */
    public function findByShiftId(int $shiftId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE shift_id = :shift_id AND deleted_at IS NULL 
                ORDER BY entry_time DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['shift_id' => $shiftId]);
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
    }

    /**
     * Buscar visitantes actualmente dentro
     */
    public function findActiveVisitors(?int $postId = null): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'inside' AND deleted_at IS NULL";
        $params = [];
        
        if ($postId !== null) {
            $sql .= " AND post_id = :post_id";
            $params['post_id'] = $postId;
        }
        
        $sql .= " ORDER BY entry_time DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
    }

    /**
     * Registrar salida de visitante
     */
    public function registerExit(int $id, \DateTime $exitTime): bool
    {
        $sql = "UPDATE {$this->table} 
                SET exit_time = :exit_time, status = 'exited', updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'exit_time' => $exitTime->format('Y-m-d H:i:s'),
            'id' => $id
        ]);
    }

    /**
     * Contar ingresos por puesto en un rango de fechas
     */
    public function countByPostAndDateRange(int $postId, string $startDate, string $endDate): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE post_id = :post_id 
                AND entry_time BETWEEN :start_date AND :end_date 
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
        /** @var AccessLog $model */
        return [
            'post_id' => $model->getPostId(),
            'user_id' => $model->getUserId(),
            'shift_id' => $model->getShiftId(),
            'visitor_name' => $model->getVisitorName(),
            'visitor_document' => $model->getVisitorDocument(),
            'visitor_company' => $model->getVisitorCompany(),
            'purpose' => $model->getPurpose(),
            'entry_time' => $model->getEntryTime()?->format('Y-m-d H:i:s'),
            'exit_time' => $model->getExitTime()?->format('Y-m-d H:i:s'),
            'vehicle_plate' => $model->getVehiclePlate(),
            'photo_path' => $model->getPhotoPath(),
            'status' => $model->getStatus() ?? 'inside'
        ];
    }
}
