<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class LocationPaths
{
    #[OA\Get(
        path: '/api/locations',
        tags: ['Locations'],
        operationId: 'listLocations',
        summary: 'Listar localizaciones',
        description: 'Obtiene un listado paginado de localizaciones con filtros opcionales.',
        parameters: [
            new OA\Parameter(name: 'name', in: 'query', description: 'Filtro por nombre (búsqueda parcial)', schema: new OA\Schema(type: 'string'), example: 'earth'),
            new OA\Parameter(name: 'type', in: 'query', description: 'Filtro por tipo (búsqueda parcial)', schema: new OA\Schema(type: 'string'), example: 'Planet'),
            new OA\Parameter(name: 'dimension', in: 'query', description: 'Filtro por dimensión (búsqueda parcial)', schema: new OA\Schema(type: 'string'), example: 'Dimension C-137'),
            new OA\Parameter(name: 'page', in: 'query', description: 'Número de página', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de localizaciones (Resource collection)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Location'),
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 7),
                                new OA\Property(property: 'per_page', type: 'integer', example: 20),
                                new OA\Property(property: 'total', type: 'integer', example: 126),
                            ],
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(): void {}

    #[OA\Get(
        path: '/api/locations/{id}',
        tags: ['Locations'],
        operationId: 'getLocation',
        summary: 'Detalle de localización',
        description: 'Obtiene el detalle completo de una localización, incluyendo residentes.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID interno de la localización', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalle de la localización',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Location',
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Localización no encontrada',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    public function show(): void {}
}
