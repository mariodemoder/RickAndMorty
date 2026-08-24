<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class EpisodePaths
{
    #[OA\Get(
        path: '/api/episodes',
        tags: ['Episodes'],
        operationId: 'listEpisodes',
        summary: 'Listar episodios',
        description: 'Obtiene un listado paginado de episodios con filtros opcionales.',
        parameters: [
            new OA\Parameter(name: 'name', in: 'query', description: 'Filtro por nombre (búsqueda parcial)', schema: new OA\Schema(type: 'string'), example: 'pilot'),
            new OA\Parameter(name: 'episode', in: 'query', description: 'Filtro por código de episodio (S01E01)', schema: new OA\Schema(type: 'string', maxLength: 10), example: 'S01E01'),
            new OA\Parameter(name: 'page', in: 'query', description: 'Número de página', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de episodios (Resource collection)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Episode'),
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 3),
                                new OA\Property(property: 'per_page', type: 'integer', example: 20),
                                new OA\Property(property: 'total', type: 'integer', example: 51),
                            ],
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(): void {}

    #[OA\Get(
        path: '/api/episodes/{id}',
        tags: ['Episodes'],
        operationId: 'getEpisode',
        summary: 'Detalle de episodio',
        description: 'Obtiene el detalle completo de un episodio, incluyendo personajes.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID interno del episodio', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalle del episodio',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Episode',
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Episodio no encontrado',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    public function show(): void {}
}
