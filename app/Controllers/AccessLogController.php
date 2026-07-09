<?php

namespace App\Controllers;

/**
 * Controlador para Registro de Ingresos (Prioridad Alta)
 * Punto de entrada para el demo funcional
 */
class AccessLogController extends BaseController
{
    /**
     * Listar todos los registros de acceso
     * GET /api/access-logs
     */
    public function index(): void
    {
        try {
            $accessLogs = $this->accessLogService->getAllAccessLogs();
            
            $this->successResponse([
                'access_logs' => array_map(fn($log) => $log->toArray(), $accessLogs)
            ], 'Registros obtenidos exitosamente');
            
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Obtener un registro específico
     * GET /api/access-logs/{id}
     */
    public function show(int $id): void
    {
        try {
            $accessLog = $this->accessLogService->getAccessLogById($id);
            
            if ($accessLog === null) {
                $this->errorResponse('Registro no encontrado', 404);
                return;
            }
            
            $this->successResponse([
                'access_log' => $accessLog->toArray()
            ]);
            
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Registrar nuevo ingreso
     * POST /api/access-logs
     */
    public function store(): void
    {
        try {
            $data = $this->getPostData();
            
            // Validar que haya usuario autenticado (en producción esto viene del session/token)
            if ($this->currentUserId === null) {
                $this->errorResponse('Usuario no autenticado', 401);
                return;
            }

            $accessLog = $this->accessLogService->registerAccess($data);
            
            $this->successResponse([
                'access_log' => $accessLog->toArray()
            ], 'Ingreso registrado exitosamente', 201);
            
        } catch (\InvalidArgumentException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (\RuntimeException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Registrar salida de visitante
     * POST /api/access-logs/{id}/exit
     */
    public function registerExit(int $id): void
    {
        try {
            // Validar que haya usuario autenticado
            if ($this->currentUserId === null) {
                $this->errorResponse('Usuario no autenticado', 401);
                return;
            }

            $result = $this->accessLogService->registerExit($id);
            
            if ($result) {
                $this->successResponse([], 'Salida registrada exitosamente');
            } else {
                $this->errorResponse('No se pudo registrar la salida', 500);
            }
            
        } catch (\RuntimeException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Obtener visitantes activos actualmente dentro
     * GET /api/access-logs/active
     */
    public function activeVisitors(?int $postId = null): void
    {
        try {
            $visitors = $this->accessLogService->getActiveVisitors($postId);
            
            $this->successResponse([
                'active_visitors' => array_map(fn($v) => $v->toArray(), $visitors),
                'count' => count($visitors)
            ], 'Visitantes activos obtenidos exitosamente');
            
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Obtener registros por puesto
     * GET /api/posts/{postId}/access-logs
     */
    public function getByPost(int $postId): void
    {
        try {
            $accessLogs = $this->accessLogService->getByPostId($postId);
            
            $this->successResponse([
                'access_logs' => array_map(fn($log) => $log->toArray(), $accessLogs)
            ], 'Registros del puesto obtenidos exitosamente');
            
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Obtener registros por turno
     * GET /api/shifts/{shiftId}/access-logs
     */
    public function getByShift(int $shiftId): void
    {
        try {
            $accessLogs = $this->accessLogService->getByShiftId($shiftId);
            
            $this->successResponse([
                'access_logs' => array_map(fn($log) => $log->toArray(), $accessLogs)
            ], 'Registros del turno obtenidos exitosamente');
            
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }
}
