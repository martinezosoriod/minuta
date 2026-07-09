<?php

namespace App\Controllers;

use App\Services\AccessLogService;
use App\Services\ShiftService;
use App\Repositories\AccessLogRepository;
use App\Repositories\ShiftRepository;
use App\Repositories\AuditLogRepository;
use App\Services\Database;

/**
 * Controlador base con inicialización de dependencias
 */
abstract class BaseController
{
    protected Database $db;
    protected AccessLogService $accessLogService;
    protected ShiftService $shiftService;
    protected ?int $currentUserId = null;
    protected ?int $currentPostId = null;

    public function __construct()
    {
        // Inicializar conexión a base de datos
        $this->db = Database::getInstance();
        $pdo = $this->db->getConnection();

        // Inicializar repositorios
        $accessLogRepo = new AccessLogRepository($pdo);
        $shiftRepo = new ShiftRepository($pdo);
        $auditLogRepo = new AuditLogRepository($pdo);

        // Inicializar servicios
        $this->accessLogService = new AccessLogService($accessLogRepo, $shiftRepo, $auditLogRepo);
        $this->shiftService = new ShiftService($shiftRepo, $auditLogRepo);
    }

    /**
     * Configurar usuario actual para el contexto de la petición
     */
    protected function setCurrentUser(int $userId, ?int $postId = null): void
    {
        $this->currentUserId = $userId;
        $this->currentPostId = $postId;

        // Propagar a los servicios
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $this->accessLogService->setCurrentUser($userId, $postId, $ipAddress, $userAgent);
        $this->shiftService->setCurrentUser($userId, $postId, $ipAddress, $userAgent);
    }

    /**
     * Responder con JSON
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Responder con error
     */
    protected function errorResponse(string $message, int $statusCode = 400): void
    {
        $this->jsonResponse([
            'success' => false,
            'error' => $message
        ], $statusCode);
    }

    /**
     * Responder con éxito
     */
    protected function successResponse(array $data = [], string $message = 'Operación exitosa'): void
    {
        $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Obtener datos del request POST
     */
    protected function getPostData(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $rawInput = file_get_contents('php://input');
            return json_decode($rawInput, true) ?? [];
        }
        
        return $_POST;
    }
}
