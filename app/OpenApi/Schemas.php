<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

class Schemas
{
    #[OA\Schema(
        schema: 'Character',
        required: ['id', 'external_id', 'name', 'status', 'species', 'gender', 'image'],
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'external_id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Rick Sanchez'),
            new OA\Property(property: 'status', type: 'string', enum: ['Alive', 'Dead', 'unknown'], example: 'Alive'),
            new OA\Property(property: 'species', type: 'string', example: 'Human'),
            new OA\Property(property: 'type', type: 'string', nullable: true, example: ''),
            new OA\Property(property: 'gender', type: 'string', enum: ['Female', 'Male', 'Genderless', 'unknown'], example: 'Male'),
            new OA\Property(property: 'image', type: 'string', format: 'uri', example: 'https://rickandmortyapi.com/api/character/avatar/1.jpeg'),
            new OA\Property(property: 'origin', ref: '#/components/schemas/Location'),
            new OA\Property(property: 'location', ref: '#/components/schemas/Location'),
            new OA\Property(
                property: 'episodes',
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/Episode'),
            ),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        ],
    )]
    public function character(): void {}

    #[OA\Schema(
        schema: 'Episode',
        required: ['id', 'external_id', 'name', 'air_date', 'episode_code'],
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'external_id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Pilot'),
            new OA\Property(property: 'air_date', type: 'string', example: 'December 2, 2013'),
            new OA\Property(property: 'episode_code', type: 'string', example: 'S01E01'),
            new OA\Property(
                property: 'characters',
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/Character'),
            ),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        ],
    )]
    public function episode(): void {}

    #[OA\Schema(
        schema: 'Location',
        required: ['id', 'external_id', 'name', 'type', 'dimension'],
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'external_id', type: 'integer', example: 1),
            new OA\Property(property: 'name', type: 'string', example: 'Earth (C-137)'),
            new OA\Property(property: 'type', type: 'string', example: 'Planet'),
            new OA\Property(property: 'dimension', type: 'string', example: 'Dimension C-137'),
            new OA\Property(
                property: 'residents',
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/Character'),
            ),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        ],
    )]
    public function location(): void {}

    #[OA\Schema(
        schema: 'SyncLog',
        required: ['id', 'status', 'locations_count', 'episodes_count', 'characters_count', 'created_at', 'updated_at'],
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'status', type: 'string', enum: ['queued', 'running', 'completed', 'failed'], example: 'completed'),
            new OA\Property(property: 'batch_id', type: 'string', nullable: true, example: 'batch-123'),
            new OA\Property(property: 'started_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'finished_at', type: 'string', format: 'date-time', nullable: true),
            new OA\Property(property: 'locations_count', type: 'integer', example: 126),
            new OA\Property(property: 'episodes_count', type: 'integer', example: 51),
            new OA\Property(property: 'characters_count', type: 'integer', example: 826),
            new OA\Property(property: 'error_message', type: 'string', nullable: true),
            new OA\Property(
                property: 'entries',
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/SyncLogEntry'),
            ),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        ],
    )]
    public function syncLog(): void {}

    #[OA\Schema(
        schema: 'SyncLogEntry',
        required: ['id', 'sync_log_id', 'level', 'message', 'created_at'],
        properties: [
            new OA\Property(property: 'id', type: 'integer', example: 1),
            new OA\Property(property: 'sync_log_id', type: 'integer', example: 1),
            new OA\Property(property: 'level', type: 'string', enum: ['info', 'warning', 'error'], example: 'info'),
            new OA\Property(property: 'message', type: 'string', example: 'Locations page 1/7: 20 synced'),
            new OA\Property(property: 'context', type: 'object', nullable: true),
            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        ],
    )]
    public function syncLogEntry(): void {}

    #[OA\Schema(
        schema: 'ErrorResponse',
        required: ['error'],
        properties: [
            new OA\Property(
                property: 'error',
                type: 'object',
                required: ['message', 'status'],
                properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Not found'),
                    new OA\Property(property: 'status', type: 'integer', example: 404),
                ],
            ),
        ],
    )]
    public function errorResponse(): void {}

    #[OA\Schema(
        schema: 'ValidationErrorResponse',
        required: ['message', 'errors'],
        properties: [
            new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
            new OA\Property(
                property: 'errors',
                type: 'object',
                additionalProperties: new OA\AdditionalProperties(
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['The email field is required.'],
                ),
            ),
        ],
    )]
    public function validationErrorResponse(): void {}
}
