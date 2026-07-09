<?php

namespace App\Models;

/**
 * Modelo base con soporte para soft deletes y trazabilidad
 */
abstract class BaseModel
{
    protected string $table;
    protected ?int $id = null;
    protected ?\DateTime $createdAt = null;
    protected ?\DateTime $updatedAt = null;
    protected ?\DateTime $deletedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTime $deletedAt): static
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    /**
     * Convertir modelo a array
     */
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        unset($vars['table']);
        return $vars;
    }
}
