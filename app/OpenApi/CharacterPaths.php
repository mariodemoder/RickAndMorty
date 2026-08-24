<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class CharacterPaths
{
    #[OA\Get(
        path: '/api/characters',
        tags: ['Characters'],
        operationId: 'listCharacters',
        summary: 'Listar personajes',
        description: 'Obtiene un listado paginado de personajes con filtros opcionales.',
        parameters: [
            new OA\Parameter(name: 'name', in: 'query', description: 'Filtro por nombre (búsqueda parcial)', schema: new OA\Schema(type: 'string'), example: 'rick'),
            new OA\Parameter(name: 'status', in: 'query', description: 'Filtro por estado', schema: new OA\Schema(type: 'string', enum: ['Alive', 'Dead', 'unknown']), example: 'Alive'),
            new OA\Parameter(name: 'species', in: 'query', description: 'Filtro por especie (búsqueda parcial)', schema: new OA\Schema(type: 'string'), example: 'Human'),
            new OA\Parameter(name: 'gender', in: 'query', description: 'Filtro por género', schema: new OA\Schema(type: 'string', enum: ['Female', 'Male', 'Genderless', 'unknown']), example: 'Male'),
            new OA\Parameter(name: 'page', in: 'query', description: 'Número de página', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de personajes (Resource collection)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Character'),
                        ),
                        new OA\Property(
                            property: 'meta',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer', example: 1),
                                new OA\Property(property: 'last_page', type: 'integer', example: 42),
                                new OA\Property(property: 'per_page', type: 'integer', example: 20),
                                new OA\Property(property: 'total', type: 'integer', example: 826),
                            ],
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(): void {}

    #[OA\Get(
        path: '/api/characters/{id}',
        tags: ['Characters'],
        operationId: 'getCharacter',
        summary: 'Detalle de personaje',
        description: 'Obtiene el detalle completo de un personaje, incluyendo ubicaciones y episodios.',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID interno del personaje', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detalle del personaje',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Character',
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Personaje no encontrado',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    public function show(): void {}
}
