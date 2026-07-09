<?php

namespace App\Models;

/**
 * Modelo AccessLog - Registro de ingresos (Prioridad Alta)
 * Punto de entrada para el demo funcional
 */
class AccessLog extends BaseModel
{
    protected string $table = 'access_logs';
    
    private ?int $postId = null;
    private ?int $userId = null;
    private ?int $shiftId = null;
    private ?string $visitorName = null;
    private ?string $visitorDocument = null;
    private ?string $visitorCompany = null;
    private ?string $purpose = null;
    private ?\DateTime $entryTime = null;
    private ?\DateTime $exitTime = null;
    private ?string $vehiclePlate = null;
    private ?string $photoPath = null;
    private ?string $status = null;

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

    public function getVisitorName(): ?string
    {
        return $this->visitorName;
    }

    public function setVisitorName(string $visitorName): static
    {
        $this->visitorName = $visitorName;
        return $this;
    }

    public function getVisitorDocument(): ?string
    {
        return $this->visitorDocument;
    }

    public function setVisitorDocument(?string $visitorDocument): static
    {
        $this->visitorDocument = $visitorDocument;
        return $this;
    }

    public function getVisitorCompany(): ?string
    {
        return $this->visitorCompany;
    }

    public function setVisitorCompany(?string $visitorCompany): static
    {
        $this->visitorCompany = $visitorCompany;
        return $this;
    }

    public function getPurpose(): ?string
    {
        return $this->purpose;
    }

    public function setPurpose(?string $purpose): static
    {
        $this->purpose = $purpose;
        return $this;
    }

    public function getEntryTime(): ?\DateTime
    {
        return $this->entryTime;
    }

    public function setEntryTime(\DateTime $entryTime): static
    {
        $this->entryTime = $entryTime;
        return $this;
    }

    public function getExitTime(): ?\DateTime
    {
        return $this->exitTime;
    }

    public function setExitTime(?\DateTime $exitTime): static
    {
        $this->exitTime = $exitTime;
        return $this;
    }

    public function getVehiclePlate(): ?string
    {
        return $this->vehiclePlate;
    }

    public function setVehiclePlate(?string $vehiclePlate): static
    {
        $this->vehiclePlate = $vehiclePlate;
        return $this;
    }

    public function getPhotoPath(): ?string
    {
        return $this->photoPath;
    }

    public function setPhotoPath(?string $photoPath): static
    {
        $this->photoPath = $photoPath;
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

    public function isInside(): bool
    {
        return $this->status === 'inside';
    }

    public function hasExited(): bool
    {
        return $this->status === 'exited';
    }
}
