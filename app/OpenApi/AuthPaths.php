<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class AuthPaths
{
    #[OA\Post(
        path: '/api/register',
        tags: ['Auth'],
        operationId: 'register',
        summary: 'Registrar nuevo usuario',
        description: 'Crea una cuenta nueva y retorna el usuario junto con un token de autenticación.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Rick Sanchez'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', description: 'Debe ser único en la tabla users', example: 'rick@example.com'),
                    new OA\Property(property: 'password', type: 'string', minLength: 8, example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', minLength: 8, example: 'password123'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario registrado exitosamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Rick Sanchez'),
                                new OA\Property(property: 'email', type: 'string', example: 'rick@example.com'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ],
                        ),
                        new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
                    ],
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Error de validación',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse'),
            ),
        ],
    )]
    public function register(): void {}

    #[OA\Post(
        path: '/api/login',
        tags: ['Auth'],
        operationId: 'login',
        summary: 'Iniciar sesión',
        description: 'Autentica un usuario y retorna un token de acceso.',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'rick@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login exitoso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Rick Sanchez'),
                                new OA\Property(property: 'email', type: 'string', example: 'rick@example.com'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ],
                        ),
                        new OA\Property(property: 'token', type: 'string', example: '1|abc123...'),
                    ],
                ),
            ),
            new OA\Response(
                response: 401,
                description: 'Credenciales inválidas',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    public function login(): void {}

    #[OA\Post(
        path: '/api/logout',
        tags: ['Auth'],
        operationId: 'logout',
        summary: 'Cerrar sesión',
        description: 'Invalida el token de autenticación actual.',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout exitoso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Logged out successfully.'),
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
    public function logout(): void {}
}
