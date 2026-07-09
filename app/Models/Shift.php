<?php

namespace App\Models;

/**
 * Modelo Shift - Turnos de trabajo
 */
class Shift extends BaseModel
{
    protected string $table = 'shifts';
    
    private ?int $postId = null;
    private ?int $userId = null;
    private ?\DateTime $startTime = null;
    private ?\DateTime $endTime = null;
    private ?string $status = null;
    private ?string $observations = null;

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): static
    {
        $this->postId = $postId;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTime $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTime $endTime): static
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): static
    {
        $this->observations = $observations;
        return $this;
    }

    public function isOpen(): bool
    {
        return $this->status === 'open' || $this->status === 'in_progress';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
