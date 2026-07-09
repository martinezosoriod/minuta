<?php

namespace App\Models;

/**
 * Modelo Document - Comunicados y Actas (Prioridad Media)
 * Sistema de gestión documental para bitácoras
 * Asegura integridad de los registros bajo Ley 527
 */
class Document extends BaseModel
{
    protected string $table = 'documents';
    
    private ?int $postId = null;
    private ?int $userId = null;
    private ?int $shiftId = null;
    private ?string $documentType = null;
    private ?string $title = null;
    private ?string $content = null;
    private ?string $filePath = null;
    private ?string $digitalSignature = null;
    private ?string $hashIntegrity = null;
    private ?bool $isSigned = null;

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(?int $postId): static
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

    public function getDocumentType(): ?string
    {
        return $this->documentType;
    }

    public function setDocumentType(string $documentType): static
    {
        $this->documentType = $documentType;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getDigitalSignature(): ?string
    {
        return $this->digitalSignature;
    }

    public function setDigitalSignature(?string $digitalSignature): static
    {
        $this->digitalSignature = $digitalSignature;
        return $this;
    }

    public function getHashIntegrity(): ?string
    {
        return $this->hashIntegrity;
    }

    public function setHashIntegrity(?string $hashIntegrity): static
    {
        $this->hashIntegrity = $hashIntegrity;
        return $this;
    }

    public function getIsSigned(): ?bool
    {
        return $this->isSigned;
    }

    public function setIsSigned(bool $isSigned): static
    {
        $this->isSigned = $isSigned;
        return $this;
    }

    /**
     * Generar hash de integridad del documento (Ley 527)
     */
    public function generateIntegrityHash(): string
    {
        $data = $this->content . $this->title . $this->getCreatedAt()?->format('Y-m-d H:i:s');
        $hash = hash('sha256', $data);
        $this->setHashIntegrity($hash);
        return $hash;
    }

    /**
     * Verificar integridad del documento
     */
    public function verifyIntegrity(): bool
    {
        if ($this->hashIntegrity === null) {
            return false;
        }
        return $this->generateIntegrityHash() === $this->hashIntegrity;
    }
}
