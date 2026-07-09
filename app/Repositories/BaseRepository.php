<?php

namespace App\Repositories;

use PDO;
use PDOException;
use App\Models\BaseModel;

/**
 * Repositorio base con operaciones CRUD y soporte para soft deletes
 */
abstract class BaseRepository
{
    protected PDO $db;
    protected string $table;
    protected string $modelClass;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Obtener todos los registros (excluyendo eliminados)
     */
    public function findAll(array $orderBy = ['id' => 'ASC']): array
    {
        $orderClause = implode(', ', array_map(
            fn($col, $dir) => "$col $dir",
            array_keys($orderBy),
            $orderBy
        ));

        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY {$orderClause}";
        $stmt = $this->db->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
    }

    /**
     * Buscar por ID
     */
    public function findById(int $id): ?BaseModel
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetchObject($this->modelClass);
        return $result ?: null;
    }

    /**
     * Crear un nuevo registro
     */
    public function create(BaseModel $model): BaseModel
    {
        $data = $this->extractData($model);
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);

        $model->setId((int) $this->db->lastInsertId());
        return $model;
    }

    /**
     * Actualizar registro existente
     */
    public function update(BaseModel $model): BaseModel
    {
        $data = $this->extractData($model);
        $sets = implode(', ', array_map(fn($col) => "$col = :$col", array_keys($data)));

        $sql = "UPDATE {$this->table} SET {$sets}, updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([...$data, 'id' => $model->getId()]);

        return $model;
    }

    /**
     * Soft delete - Marcado como eliminado sin borrar físicamente
     * Requisito de seguridad: Prohibido borrado lógico inconsistente
     */
    public function softDelete(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET deleted_at = CURRENT_TIMESTAMP 
                WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Eliminar permanentemente (solo para administración)
     */
    public function hardDelete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Contar registros activos
     */
    public function count(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Extraer datos del modelo para inserción/actualización
     */
    abstract protected function extractData(BaseModel $model): array;

    /**
     * Mapear resultado de BD a modelo
     */
    protected function mapToModel(array $data): BaseModel
    {
        $model = new $this->modelClass();
        
        foreach ($data as $key => $value) {
            $setter = 'set' . ucfirst($this->snakeToCamel($key));
            if (method_exists($model, $setter)) {
                if ($value !== null && in_array($key, ['created_at', 'updated_at', 'deleted_at', 'start_time', 'end_time', 'entry_time', 'exit_time', 'reported_at', 'resolved_at'], true)) {
                    $value = new \DateTime($value);
                }
                $model->$setter($value);
            }
        }
        
        return $model;
    }

    /**
     * Convertir snake_case a camelCase
     */
    private function snakeToCamel(string $string): string
    {
        return lcfirst(str_replace('_', '', ucwords($string, '_')));
    }
}
