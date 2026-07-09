<?php

namespace App\Repositories;

use PDO;
use App\Models\AuditLog;
use App\Models\BaseModel;

/**
 * Repositorio para AuditLog - Trazabilidad de operaciones
 * Requisito: Obligatoria en toda transacción
 */
class AuditLogRepository extends BaseRepository
{
    protected string $table = 'audit_logs';
    protected string $modelClass = AuditLog::class;

    public function __construct(PDO $db)
    {
        parent::__construct($db);
    }

    /**
     * Registrar una acción de auditoría
     * Middleware para registrar usuario, timestamp y puesto_id
     */
    public function logAction(
        string $action,
        string $tableName,
        ?int $userId = null,
        ?int $postId = null,
        ?int $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): AuditLog {
        $auditLog = new AuditLog();
        $auditLog->setAction($action)
                 ->setTableName($tableName)
                 ->setUserId($userId)
                 ->setPostId($postId)
                 ->setRecordId($recordId)
                 ->setOldValues($oldValues)
                 ->setNewValues($newValues)
                 ->setIpAddress($ipAddress)
                 ->setUserAgent($userAgent);

        return $this->create($auditLog);
    }

    /**
     * Buscar logs por usuario
     */
    public function findByUserId(int $userId, int $limit = 100): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
    }

    /**
     * Buscar logs por puesto
     */
    public function findByPostId(int $postId, int $limit = 100): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE post_id = :post_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('post_id', $postId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
    }

    /**
     * Buscar logs por tabla
     */
    public function findByTableName(string $tableName, int $limit = 100): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE table_name = :table_name 
                ORDER BY created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('table_name', $tableName, PDO::PARAM_STR);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
    }

    /**
     * Buscar logs por rango de fechas
     */
    public function findByDateRange(string $startDate, string $endDate, int $limit = 100): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE created_at BETWEEN :start_date AND :end_date 
                ORDER BY created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('start_date', $startDate, PDO::PARAM_STR);
        $stmt->bindValue('end_date', $endDate, PDO::PARAM_STR);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
    }

    /**
     * Buscar logs por acción
     */
    public function findByAction(string $action, int $limit = 100): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE action = :action 
                ORDER BY created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue('action', $action, PDO::PARAM_STR);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
    }

    /**
     * Obtener resumen de actividades por usuario
     */
    public function getActivitySummaryByUser(int $userId, string $startDate, string $endDate): array
    {
        $sql = "SELECT action, COUNT(*) as count 
                FROM {$this->table} 
                WHERE user_id = :user_id 
                AND created_at BETWEEN :start_date AND :end_date 
                GROUP BY action 
                ORDER BY count DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    protected function extractData(BaseModel $model): array
    {
        /** @var AuditLog $model */
        return [
            'user_id' => $model->getUserId(),
            'post_id' => $model->getPostId(),
            'action' => $model->getAction(),
            'table_name' => $model->getTableName(),
            'record_id' => $model->getRecordId(),
            'old_values' => $model->getOldValues() !== null ? json_encode($model->getOldValues()) : null,
            'new_values' => $model->getNewValues() !== null ? json_encode($model->getNewValues()) : null,
            'ip_address' => $model->getIpAddress(),
            'user_agent' => $model->getUserAgent()
        ];
    }
}
