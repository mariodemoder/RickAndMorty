<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class FavoritePaths
{
    #[OA\Get(
        path: '/api/favorites',
        tags: ['Favorites'],
        operationId: 'listFavorites',
        summary: 'Listar favoritos',
        description: 'Obtiene los personajes favoritos del usuario autenticado.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', description: 'Número de página', schema: new OA\Schema(type: 'integer', default: 1)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Listado de favoritos (Resource collection)',
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
                                new OA\Property(property: 'last_page', type: 'integer', example: 1),
                                new OA\Property(property: 'per_page', type: 'integer', example: 20),
                                new OA\Property(property: 'total', type: 'integer', example: 5),
                            ],
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/favorites',
        tags: ['Favorites'],
        operationId: 'addFavorite',
        summary: 'Añadir personaje a favoritos',
        description: 'Añade un personaje a la lista de favoritos del usuario autenticado.',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['character_id'],
                properties: [
                    new OA\Property(property: 'character_id', type: 'integer', description: 'ID del personaje (debe existir en la tabla characters)', example: 1),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Favorito añadido exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Favorite added successfully.'),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Character',
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validación',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse'),
            ),
        ],
    )]
    public function store(): void {}

    #[OA\Delete(
        path: '/api/favorites/{id}',
        tags: ['Favorites'],
        operationId: 'removeFavorite',
        summary: 'Eliminar favorito',
        description: 'Elimina un personaje de la lista de favoritos del usuario autenticado.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'ID del personaje', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Favorito eliminado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Favorite removed successfully.'),
                    ],
                ),
            ),
            new OA\Response(
                response: 401,
                description: 'No autenticado',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
            new OA\Response(
                response: 404,
                description: 'Favorito no encontrado',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    public function destroy(): void {}
}
