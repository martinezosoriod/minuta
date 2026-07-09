<?php

namespace App\Controllers;

/**
 * Controlador para Entrega de Turno (Prioridad Crítica)
 * Desarrollar lógica de cierre y apertura de turno asegurando continuidad
 */
class ShiftController extends BaseController
{
    /**
     * Listar historial de turnos por puesto
     * GET /api/posts/{postId}/shifts
     */
    public function index(int $postId): void
    {
        try {
            $shifts = $this->shiftService->getShiftHistory($postId);
            
            $this->successResponse([
                'shifts' => array_map(fn($s) => $s->toArray(), $shifts)
            ], 'Historial de turnos obtenido exitosamente');
            
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Obtener turno activo por puesto
     * GET /api/posts/{postId}/shifts/active
     */
    public function activeShift(int $postId): void
    {
        try {
            $shift = $this->shiftService->getActiveShiftByPostId($postId);
            
            if ($shift === null) {
                $this->successResponse([
                    'shift' => null,
                    'has_active' => false
                ], 'No hay turno activo para este puesto');
                return;
            }
            
            $this->successResponse([
                'shift' => $shift->toArray(),
                'has_active' => true
            ], 'Turno activo obtenido exitosamente');
            
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Abrir nuevo turno
     * POST /api/posts/{postId}/shifts/open
     */
    public function open(int $postId): void
    {
        try {
            // Validar que haya usuario autenticado
            if ($this->currentUserId === null) {
                $this->errorResponse('Usuario no autenticado', 401);
                return;
            }

            $data = $this->getPostData();
            $observations = $data['observations'] ?? null;

            $shift = $this->shiftService->openShift($postId, $this->currentUserId, $observations);
            
            $this->successResponse([
                'shift' => $shift->toArray()
            ], 'Turno abierto exitosamente', 201);
            
        } catch (\RuntimeException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Cerrar turno
     * POST /api/shifts/{id}/close
     */
    public function close(int $id): void
    {
        try {
            // Validar que haya usuario autenticado
            if ($this->currentUserId === null) {
                $this->errorResponse('Usuario no autenticado', 401);
                return;
            }

            $data = $this->getPostData();
            $observations = $data['observations'] ?? null;

            $result = $this->shiftService->closeShift($id, $observations);
            
            if ($result) {
                $this->successResponse([], 'Turno cerrado exitosamente');
            } else {
                $this->errorResponse('No se pudo cerrar el turno', 500);
            }
            
        } catch (\RuntimeException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Realizar entrega de turno (cierre + apertura atómica)
     * POST /api/shifts/{previousShiftId}/handover
     */
    public function handover(int $previousShiftId): void
    {
        try {
            // Validar que haya usuario autenticado
            if ($this->currentUserId === null) {
                $this->errorResponse('Usuario no autenticado', 401);
                return;
            }

            $data = $this->getPostData();
            
            if (empty($data['new_user_id'])) {
                $this->errorResponse('El ID del nuevo vigilante es obligatorio', 400);
                return;
            }

            if (empty($data['handover_notes'])) {
                $this->errorResponse('Las notas de entrega son obligatorias', 400);
                return;
            }

            $newShift = $this->shiftService->handoverShift(
                previousShiftId: $previousShiftId,
                newUserId: (int) $data['new_user_id'],
                handoverNotes: $data['handover_notes'],
                newObservations: $data['new_observations'] ?? null
            );
            
            $this->successResponse([
                'new_shift' => $newShift->toArray()
            ], 'Entrega de turno realizada exitosamente');
            
        } catch (\RuntimeException $e) {
            $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Marcar turno como en progreso
     * POST /api/shifts/{id}/in-progress
     */
    public function markInProgress(int $id): void
    {
        try {
            $result = $this->shiftService->markShiftAsInProgress($id);
            
            if ($result) {
                $this->successResponse([], 'Turno marcado como en progreso');
            } else {
                $this->errorResponse('No se pudo actualizar el turno', 500);
            }
            
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Verificar si hay turno activo en un puesto
     * GET /api/posts/{postId}/shifts/check
     */
    public function hasActiveShift(int $postId): void
    {
        try {
            $hasActive = $this->shiftService->hasActiveShift($postId);
            
            $this->successResponse([
                'has_active_shift' => $hasActive,
                'post_id' => $postId
            ]);
            
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }
}
