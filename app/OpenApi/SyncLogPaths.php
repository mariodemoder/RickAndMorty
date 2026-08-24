<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class SyncLogPaths
{
    #[OA\Get(
        path: '/api/sync/logs',
        tags: ['Sync Logs'],
        operationId: 'listSyncLogs',
        summary: 'Listar logs de sincronización',
        description: 'Obtiene un listado paginado de ejecuciones de sincronización. El formato de paginación es de Laravel.paginator (campos en raíz, no en `meta`).',
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', description: 'Elementos por página', schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'page', in: 'query', description: 'Número de página', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de logs de sincronización (paginator)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/SyncLog'),
                        ),
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(property: 'last_page', type: 'integer', example: 1),
                        new OA\Property(property: 'per_page', type: 'integer', example: 15),
                        new OA\Property(property: 'total', type: 'integer', example: 10),
                    ],
                ),
            ),
        ],
    )]
    public function index(): void {}

    #[OA\Get(
        path: '/api/sync/logs/{syncLog}',
        tags: ['Sync Logs'],
        operationId: 'getSyncLog',
        summary: 'Detalle de log de sincronización',
        description: 'Obtiene el detalle completo de una ejecución de sincronización, incluyendo entries.',
        parameters: [
            new OA\Parameter(name: 'syncLog', in: 'path', required: true, description: 'ID del log de sincronización', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalle del log de sincronización',
                content: new OA\JsonContent(ref: '#/components/schemas/SyncLog'),
            ),
            new OA\Response(
                response: 404,
                description: 'Log no encontrado',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    public function show(): void {}
}
