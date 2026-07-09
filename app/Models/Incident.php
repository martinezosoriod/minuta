<?php

namespace App\Models;

/**
 * Modelo Incident - Novedades/Reportes de incidencias (Prioridad Media)
 */
class Incident extends BaseModel
{
    protected string $table = 'incidents';
    
    private ?int $postId = null;
    private ?int $userId = null;
    private ?int $shiftId = null;
    private ?string $incidentType = null;
    private ?string $severity = null;
    private ?string $title = null;
    private ?string $description = null;
    private ?string $actionTaken = null;
    private ?string $status = null;
    private ?\DateTime $reportedAt = null;
    private ?\DateTime $resolvedAt = null;

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

    public function getShiftId(): ?int
    {
        return $this->shiftId;
    }

    public function setShiftId(?int $shiftId): static
    {
        $this->shiftId = $shiftId;
        return $this;
    }

    public function getIncidentType(): ?string
    {
        return $this->incidentType;
    }

    public function setIncidentType(string $incidentType): static
    {
        $this->incidentType = $incidentType;
        return $this;
    }

    public function getSeverity(): ?string
    {
        return $this->severity;
    }

    public function setSeverity(string $severity): static
    {
        $this->severity = $severity;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getActionTaken(): ?string
    {
        return $this->actionTaken;
    }

    public function setActionTaken(?string $actionTaken): static
    {
        $this->actionTaken = $actionTaken;
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

    public function getReportedAt(): ?\DateTime
    {
        return $this->reportedAt;
    }

    public function setReportedAt(\DateTime $reportedAt): static
    {
        $this->reportedAt = $reportedAt;
        return $this;
    }

    public function getResolvedAt(): ?\DateTime
    {
        return $this->resolvedAt;
    }

    public function setResolvedAt(?\DateTime $resolvedAt): static
    {
        $this->resolvedAt = $resolvedAt;
        return $this;
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'in_progress'], true);
    }

    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'closed'], true);
    }
}
