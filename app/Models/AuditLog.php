<?php

namespace App\Models;

/**
 * Modelo AuditLog - Trazabilidad de operaciones
 * Requisito: Obligatoria en toda transacción
 */
class AuditLog extends BaseModel
{
    protected string $table = 'audit_logs';
    
    private ?int $userId = null;
    private ?int $postId = null;
    private ?string $action = null;
    private ?string $tableName = null;
    private ?int $recordId = null;
    private ?array $oldValues = null;
    private ?array $newValues = null;
    private ?string $ipAddress = null;
    private ?string $userAgent = null;

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(?int $postId): static
    {
        $this->postId = $postId;
        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName): static
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function getRecordId(): ?int
    {
        return $this->recordId;
    }

    public function setRecordId(?int $recordId): static
    {
        $this->recordId = $recordId;
        return $this;
    }

    public function getOldValues(): ?array
    {
        return $this->oldValues;
    }

    public function setOldValues(?array $oldValues): static
    {
        $this->oldValues = $oldValues;
        return $this;
    }

    public function getNewValues(): ?array
    {
        return $this->newValues;
    }

    public function setNewValues(?array $newValues): static
    {
        $this->newValues = $newValues;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }
}
