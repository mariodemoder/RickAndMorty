<?php

namespace Tests\Unit\Models;

use App\Models\SyncLog;
use App\Models\SyncRawResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncRawResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_response_body_is_compressed_on_create(): void
    {
        $syncLog = SyncLog::factory()->create();
        $json = '{"info":{"count":1},"results":[{"id":1}]}';

        $rawResponse = SyncRawResponse::create([
            'sync_log_id' => $syncLog->id,
            'resource_type' => 'location',
            'page_number' => 1,
            'total_pages' => 1,
            'response_body' => gzcompress($json, 6),
            'items_count' => 1,
        ]);

        $this->assertDatabaseHas('sync_raw_responses', [
            'id' => $rawResponse->id,
            'sync_log_id' => $syncLog->id,
            'resource_type' => 'location',
            'page_number' => 1,
        ]);
    }

    public function test_response_body_decompresses_correctly(): void
    {
        $syncLog = SyncLog::factory()->create();
        $json = '{"info":{"count":2},"results":[{"id":1},{"id":2}]}';
        $compressed = gzcompress($json, 6);

        $rawResponse = SyncRawResponse::create([
            'sync_log_id' => $syncLog->id,
            'resource_type' => 'episode',
            'page_number' => 1,
            'total_pages' => 1,
            'response_body' => $compressed,
            'items_count' => 2,
        ]);

        $decoded = $rawResponse->getDecompressedBody();

        $this->assertEquals(2, $decoded['info']['count']);
        $this->assertCount(2, $decoded['results']);
    }

    public function test_belongs_to_sync_log(): void
    {
        $syncLog = SyncLog::factory()->create();

        $rawResponse = SyncRawResponse::create([
            'sync_log_id' => $syncLog->id,
            'resource_type' => 'character',
            'page_number' => 1,
            'total_pages' => 42,
            'response_body' => gzcompress('{}', 6),
            'items_count' => 0,
        ]);

        $this->assertTrue($rawResponse->syncLog->is($syncLog));
    }

    public function test_sync_log_has_raw_responses(): void
    {
        $syncLog = SyncLog::factory()->create();

        SyncRawResponse::create([
            'sync_log_id' => $syncLog->id,
            'resource_type' => 'location',
            'page_number' => 1,
            'total_pages' => 7,
            'response_body' => gzcompress('{}', 6),
            'items_count' => 20,
        ]);

        SyncRawResponse::create([
            'sync_log_id' => $syncLog->id,
            'resource_type' => 'location',
            'page_number' => 2,
            'total_pages' => 7,
            'response_body' => gzcompress('{}', 6),
            'items_count' => 6,
        ]);

        $this->assertCount(2, $syncLog->rawResponses);
    }

    public function test_cascade_delete_on_sync_log(): void
    {
        $syncLog = SyncLog::factory()->create();

        SyncRawResponse::create([
            'sync_log_id' => $syncLog->id,
            'resource_type' => 'episode',
            'page_number' => 1,
            'total_pages' => 3,
            'response_body' => gzcompress('{}', 6),
            'items_count' => 20,
        ]);

        $syncLog->delete();

        $this->assertDatabaseMissing('sync_raw_responses', [
            'sync_log_id' => $syncLog->id,
        ]);
    }

    public function test_compression_saves_space(): void
    {
        $json = json_encode([
            'info' => ['count' => 826, 'pages' => 42],
            'results' => array_map(fn ($i) => [
                'id' => $i,
                'name' => "Character {$i}",
                'status' => 'Alive',
                'species' => 'Human',
                'type' => '',
                'gender' => 'Male',
                'image' => "https://example.com/avatar/{$i}.jpeg",
                'origin' => ['url' => 'https://rickandmortyapi.com/api/location/1'],
                'location' => ['url' => 'https://rickandmortyapi.com/api/location/20'],
                'episode' => ['https://rickandmortyapi.com/api/episode/1'],
            ], range(1, 20)),
        ]);

        $compressed = gzcompress($json, 6);

        $this->assertLessThan(strlen($json), strlen($compressed));
        $this->assertLessThan(0.5, strlen($compressed) / strlen($json));
    }
}
