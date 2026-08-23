<?php

namespace Tests\Feature\Api;

use App\Models\SyncLog;
use App\Models\SyncLogEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_sync_logs(): void
    {
        SyncLog::factory()->count(3)->create();

        $response = $this->getJson('/api/sync/logs');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'status', 'started_at', 'finished_at'],
                ],
                'current_page',
                'last_page',
                'per_page',
                'total',
            ]);
    }

    public function test_can_get_sync_log_detail(): void
    {
        $syncLog = SyncLog::factory()->create([
            'status' => 'completed',
            'locations_count' => 126,
            'episodes_count' => 51,
            'characters_count' => 826,
        ]);

        SyncLogEntry::create([
            'sync_log_id' => $syncLog->id,
            'level' => 'info',
            'message' => 'Sync completed successfully',
            'created_at' => now(),
        ]);

        $response = $this->getJson("/api/sync/logs/{$syncLog->id}");

        $response->assertOk()
            ->assertJson([
                'id' => $syncLog->id,
                'status' => 'completed',
                'locations_count' => 126,
                'episodes_count' => 51,
                'characters_count' => 826,
            ]);
    }

    public function test_returns_404_for_nonexistent_sync_log(): void
    {
        $response = $this->getJson('/api/sync/logs/9999');

        $response->assertStatus(404);
    }

    public function test_sync_log_detail_includes_entries(): void
    {
        $syncLog = SyncLog::factory()->create();

        SyncLogEntry::create([
            'sync_log_id' => $syncLog->id,
            'level' => 'info',
            'message' => 'Starting sync',
            'created_at' => now(),
        ]);

        SyncLogEntry::create([
            'sync_log_id' => $syncLog->id,
            'level' => 'info',
            'message' => 'Sync completed',
            'created_at' => now(),
        ]);

        $response = $this->getJson("/api/sync/logs/{$syncLog->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'entries');
    }
}
