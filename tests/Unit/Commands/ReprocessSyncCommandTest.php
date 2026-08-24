<?php

namespace Tests\Unit\Commands;

use App\Models\Episode;
use App\Models\Location;
use App\Models\Character;
use App\Models\SyncLog;
use App\Models\SyncRawResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReprocessSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_reprocess_creates_new_sync_log_from_raw_responses(): void
    {
        $syncLog = SyncLog::factory()->create(['status' => 'completed']);

        SyncRawResponse::create([
            'sync_log_id' => $syncLog->id,
            'resource_type' => 'location',
            'page_number' => 1,
            'total_pages' => 1,
            'response_body' => gzcompress(json_encode([
                'info' => ['count' => 2, 'pages' => 1],
                'results' => [
                    ['id' => 1, 'name' => 'Earth', 'type' => 'Planet', 'dimension' => 'C-137'],
                    ['id' => 2, 'name' => 'Mars', 'type' => 'Planet', 'dimension' => 'C-137'],
                ],
            ]), 6),
            'items_count' => 2,
        ]);

        $countBefore = SyncLog::count();

        $this->artisan('sync:reprocess', ['syncLogId' => $syncLog->id])
            ->assertExitCode(0);

        $this->assertDatabaseCount('locations', 2);
        $this->assertDatabaseHas('locations', ['external_id' => 1, 'name' => 'Earth']);
        $this->assertDatabaseHas('locations', ['external_id' => 2, 'name' => 'Mars']);

        $this->assertEquals($countBefore + 1, SyncLog::count());
        $newSyncLog = SyncLog::where('id', '>', $syncLog->id)->latest('id')->first();
        $this->assertNotNull($newSyncLog);
        $this->assertEquals('completed', $newSyncLog->status);
        $this->assertEquals(2, $newSyncLog->locations_count);
    }

    public function test_reprocess_with_resource_filter(): void
    {
        $syncLog = SyncLog::factory()->create(['status' => 'completed']);

        SyncRawResponse::create([
            'sync_log_id' => $syncLog->id,
            'resource_type' => 'location',
            'page_number' => 1,
            'total_pages' => 1,
            'response_body' => gzcompress(json_encode([
                'info' => ['count' => 1, 'pages' => 1],
                'results' => [
                    ['id' => 1, 'name' => 'Earth', 'type' => 'Planet', 'dimension' => 'C-137'],
                ],
            ]), 6),
            'items_count' => 1,
        ]);

        SyncRawResponse::create([
            'sync_log_id' => $syncLog->id,
            'resource_type' => 'episode',
            'page_number' => 1,
            'total_pages' => 1,
            'response_body' => gzcompress(json_encode([
                'info' => ['count' => 1, 'pages' => 1],
                'results' => [
                    ['id' => 1, 'name' => 'Pilot', 'air_date' => '2013-12-02', 'episode' => 'S01E01'],
                ],
            ]), 6),
            'items_count' => 1,
        ]);

        $this->artisan('sync:reprocess', ['syncLogId' => $syncLog->id, '--resource' => 'location'])
            ->assertExitCode(0);

        $this->assertDatabaseCount('locations', 1);
        $this->assertDatabaseCount('episodes', 0);
    }

    public function test_reprocess_returns_failure_for_nonexistent_sync_log(): void
    {
        $this->artisan('sync:reprocess', ['syncLogId' => 99999])
            ->assertExitCode(1);
    }

    public function test_reprocess_returns_failure_for_empty_raw_responses(): void
    {
        $syncLog = SyncLog::factory()->create(['status' => 'completed']);

        $this->artisan('sync:reprocess', ['syncLogId' => $syncLog->id])
            ->assertExitCode(1);
    }

    public function test_reprocess_preserves_character_episode_relationships(): void
    {
        $syncLog = SyncLog::factory()->create(['status' => 'completed']);

        SyncRawResponse::create([
            'sync_log_id' => $syncLog->id,
            'resource_type' => 'episode',
            'page_number' => 1,
            'total_pages' => 1,
            'response_body' => gzcompress(json_encode([
                'info' => ['count' => 1, 'pages' => 1],
                'results' => [
                    ['id' => 1, 'name' => 'Pilot', 'air_date' => '2013-12-02', 'episode' => 'S01E01'],
                ],
            ]), 6),
            'items_count' => 1,
        ]);

        SyncRawResponse::create([
            'sync_log_id' => $syncLog->id,
            'resource_type' => 'character',
            'page_number' => 1,
            'total_pages' => 1,
            'response_body' => gzcompress(json_encode([
                'info' => ['count' => 1, 'pages' => 1],
                'results' => [
                    [
                        'id' => 1,
                        'name' => 'Rick',
                        'status' => 'Alive',
                        'species' => 'Human',
                        'type' => '',
                        'gender' => 'Male',
                        'image' => 'img.jpg',
                        'origin' => ['url' => ''],
                        'location' => ['url' => ''],
                        'episode' => ['https://rickandmortyapi.com/api/episode/1'],
                    ],
                ],
            ]), 6),
            'items_count' => 1,
        ]);

        $this->artisan('sync:reprocess', ['syncLogId' => $syncLog->id])
            ->assertExitCode(0);

        $character = Character::first();
        $this->assertNotNull($character);
        $this->assertCount(1, $character->episodes);
        $this->assertEquals(1, $character->episodes->first()->external_id);
    }
}
