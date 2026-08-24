<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

/**
 * Rick & Morty API - Documentación OpenAPI
 *
 * API para consultar datos sincronizados de la API externa de Rick & Morty.
 * Incluye autenticación, CRUD de favoritos, y consulta de personajes, episodios y localizaciones.
 */
#[OA\Info(
    title: 'Rick & Morty API',
    version: '1.0.0',
    description: 'API para consultar datos sincronizados de la API externa de Rick & Morty. Incluye autenticación, CRUD de favoritos, y consulta de personajes, episodios y localizaciones.',
    contact: new OA\Contact(name: 'Quental', email: 'dev@quental.com'),
)]
#[OA\Server(
    url: 'http://localhost:8080',
    description: 'Servidor de desarrollo (Laravel Sail)',
)]
#[OA\Tag(name: 'Auth', description: 'Registro, login y logout de usuarios')]
#[OA\Tag(name: 'Characters', description: 'Consulta de personajes')]
#[OA\Tag(name: 'Episodes', description: 'Consulta de episodios')]
#[OA\Tag(name: 'Locations', description: 'Consulta de localizaciones')]
#[OA\Tag(name: 'Favorites', description: 'Gestión de favoritos (autenticado)')]
#[OA\Tag(name: 'Sync Logs', description: 'Logs de sincronización con la API externa')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
   bearerFormat: 'Sanctum Token',
    description: 'Token de autenticación obtenido vía POST /api/login o POST /api/register',
)]
class OpenApi
{
}
