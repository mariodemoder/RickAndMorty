<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenApiTest extends TestCase
{
    use RefreshDatabase;

    private array $spec;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('l5-swagger:generate');
        $json = file_get_contents(storage_path('api-docs/api-docs.json'));
        $this->spec = json_decode($json, true);
    }

    public function test_openapi_version_is_3_0(): void
    {
        $this->assertArrayHasKey('openapi', $this->spec);
        $this->assertEquals('3.0.0', $this->spec['openapi']);
    }

    public function test_info_title_is_correct(): void
    {
        $this->assertEquals('Rick & Morty API', $this->spec['info']['title']);
        $this->assertEquals('1.0.0', $this->spec['info']['version']);
    }

    public function test_all_expected_paths_are_documented(): void
    {
        $expectedPaths = [
            '/api/register',
            '/api/login',
            '/api/logout',
            '/api/characters',
            '/api/characters/{id}',
            '/api/episodes',
            '/api/episodes/{id}',
            '/api/locations',
            '/api/locations/{id}',
            '/api/favorites',
            '/api/favorites/{id}',
            '/api/sync/logs',
            '/api/sync/logs/{syncLog}',
        ];

        $actualPaths = array_keys($this->spec['paths']);

        foreach ($expectedPaths as $path) {
            $this->assertArrayHasKey(
                $path,
                $this->spec['paths'],
                "Path {$path} is missing from the OpenAPI spec."
            );
        }
    }

    public function test_all_expected_schemas_exist(): void
    {
        $expectedSchemas = [
            'Character',
            'Episode',
            'Location',
            'SyncLog',
            'SyncLogEntry',
            'ErrorResponse',
            'ValidationErrorResponse',
        ];

        $actualSchemas = array_keys($this->spec['components']['schemas'] ?? []);

        foreach ($expectedSchemas as $schema) {
            $this->assertArrayHasKey(
                $schema,
                $this->spec['components']['schemas'],
                "Schema {$schema} is missing from the OpenAPI spec."
            );
        }
    }

    public function test_schemas_have_required_fields(): void
    {
        $schemas = $this->spec['components']['schemas'];

        $this->assertArrayHasKey('required', $schemas['Character']);
        $this->assertContains('name', $schemas['Character']['required']);
        $this->assertContains('status', $schemas['Character']['required']);

        $this->assertArrayHasKey('required', $schemas['Episode']);
        $this->assertContains('name', $schemas['Episode']['required']);
        $this->assertContains('episode_code', $schemas['Episode']['required']);

        $this->assertArrayHasKey('required', $schemas['Location']);
        $this->assertContains('name', $schemas['Location']['required']);
    }

    public function test_security_scheme_is_bearer(): void
    {
        $securitySchemes = $this->spec['components']['securitySchemes'];

        $this->assertArrayHasKey('sanctum', $securitySchemes);
        $this->assertEquals('http', $securitySchemes['sanctum']['type']);
        $this->assertEquals('bearer', $securitySchemes['sanctum']['scheme']);
    }

    public function test_protected_endpoints_have_sanctum_security(): void
    {
        $protectedPaths = [
            '/api/logout' => ['post'],
            '/api/favorites' => ['get', 'post'],
            '/api/favorites/{id}' => ['delete'],
        ];

        foreach ($protectedPaths as $path => $methods) {
            foreach ($methods as $method) {
                $operation = $this->spec['paths'][$path][$method] ?? null;
                $this->assertNotNull(
                    $operation,
                    "Operation {$method} {$path} not found."
                );
                $this->assertArrayHasKey(
                    'security',
                    $operation,
                    "Operation {$method} {$path} is missing security."
                );
                $this->assertEquals(
                    [['sanctum' => []]],
                    $operation['security'],
                    "Operation {$method} {$path} does not use sanctum."
                );
            }
        }
    }

    public function test_public_endpoints_have_no_security(): void
    {
        $publicPaths = [
            '/api/register' => ['post'],
            '/api/login' => ['post'],
            '/api/characters' => ['get'],
            '/api/characters/{id}' => ['get'],
            '/api/episodes' => ['get'],
            '/api/episodes/{id}' => ['get'],
            '/api/locations' => ['get'],
            '/api/locations/{id}' => ['get'],
            '/api/sync/logs' => ['get'],
            '/api/sync/logs/{syncLog}' => ['get'],
        ];

        foreach ($publicPaths as $path => $methods) {
            foreach ($methods as $method) {
                $operation = $this->spec['paths'][$path][$method] ?? null;
                $this->assertNotNull(
                    $operation,
                    "Operation {$method} {$path} not found."
                );
                $this->assertArrayNotHasKey(
                    'security',
                    $operation,
                    "Public operation {$method} {$path} should not have security."
                );
            }
        }
    }

    public function test_all_endpoints_have_operation_ids(): void
    {
        foreach ($this->spec['paths'] as $path => $methods) {
            foreach ($methods as $method => $operation) {
                if (in_array($method, ['parameters', 'summary', 'description'])) {
                    continue;
                }
                $this->assertArrayHasKey(
                    'operationId',
                    $operation,
                    "Operation {$method} {$path} is missing operationId."
                );
                $this->assertNotEmpty(
                    $operation['operationId'],
                    "Operation {$method} {$path} has empty operationId."
                );
            }
        }
    }

    public function test_all_endpoints_have_tags(): void
    {
        foreach ($this->spec['paths'] as $path => $methods) {
            foreach ($methods as $method => $operation) {
                if (in_array($method, ['parameters', 'summary', 'description'])) {
                    continue;
                }
                $this->assertArrayHasKey(
                    'tags',
                    $operation,
                    "Operation {$method} {$path} is missing tags."
                );
            }
        }
    }

    public function test_sync_logs_index_has_correct_pagination_format(): void
    {
        $operation = $this->spec['paths']['/api/sync/logs']['get'];
        $response200 = $operation['responses']['200'];
        $schema = $response200['content']['application/json']['schema'];

        $this->assertArrayHasKey('data', $schema['properties']);
        $this->assertArrayHasKey('current_page', $schema['properties']);
        $this->assertArrayHasKey('last_page', $schema['properties']);
        $this->assertArrayHasKey('per_page', $schema['properties']);
        $this->assertArrayHasKey('total', $schema['properties']);
        $this->assertArrayNotHasKey('meta', $schema['properties']);
    }

    public function test_characters_index_has_correct_pagination_format(): void
    {
        $operation = $this->spec['paths']['/api/characters']['get'];
        $response200 = $operation['responses']['200'];
        $schema = $response200['content']['application/json']['schema'];

        $this->assertArrayHasKey('data', $schema['properties']);
        $this->assertArrayHasKey('meta', $schema['properties']);
        $this->assertEquals('object', $schema['properties']['meta']['type']);

        $metaProps = $schema['properties']['meta']['properties'];
        $this->assertArrayHasKey('current_page', $metaProps);
        $this->assertArrayHasKey('last_page', $metaProps);
        $this->assertArrayHasKey('per_page', $metaProps);
        $this->assertArrayHasKey('total', $metaProps);
    }

    public function test_error_responses_use_error_response_schema(): void
    {
        $errorEndpoints = [
            '/api/characters/{id}' => 'get',
            '/api/episodes/{id}' => 'get',
            '/api/locations/{id}' => 'get',
            '/api/sync/logs/{syncLog}' => 'get',
            '/api/favorites/{id}' => 'delete',
        ];

        foreach ($errorEndpoints as $path => $method) {
            $operation = $this->spec['paths'][$path][$method];
            $this->assertArrayHasKey('404', $operation['responses']);
            $response404 = $operation['responses']['404'];
            $this->assertArrayHasKey('content', $response404);
            $schema = $response404['content']['application/json']['schema'];
            $this->assertEquals(
                '#/components/schemas/ErrorResponse',
                $schema['$ref'],
                "404 response on {$method} {$path} does not use ErrorResponse schema."
            );
        }
    }

    public function test_validation_error_response_schema_exists(): void
    {
        $schema = $this->spec['components']['schemas']['ValidationErrorResponse'];
        $this->assertArrayHasKey('required', $schema);
        $this->assertContains('message', $schema['required']);
        $this->assertContains('errors', $schema['required']);
    }
}
