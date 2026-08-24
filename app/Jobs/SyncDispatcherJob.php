<?php

namespace App\Jobs;

use App\Models\Character;
use App\Models\Episode;
use App\Models\Location;
use App\Models\SyncLog;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class SyncDispatcherJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function handle(): void
    {
        echo "\n";
        echo "==================================================\n";
        echo "  SYNC DISPATCHER - Iniciando batch de sincronización\n";
        echo "==================================================\n";
        echo "\n";

        $syncLog = SyncLog::create([
            'status' => 'queued',
            'started_at' => now(),
        ]);

        echo "[Dispatcher] SyncLog #{$syncLog->id} creado (status: queued)\n";
        echo "[Dispatcher] Dispatchando batch con 3 jobs...\n";
        echo "  1. SyncLocationsJob\n";
        echo "  2. SyncEpisodesJob\n";
        echo "  3. SyncCharactersJob\n";
        echo "\n";

        Log::channel('sync')->info('Sync batch dispatched', ['sync_log_id' => $syncLog->id]);

        $batch = Bus::batch([
            new SyncLocationsJob($syncLog),
            new SyncEpisodesJob($syncLog),
            new SyncCharactersJob($syncLog),
        ])
            ->name('rick-and-morty-sync')
            ->then(function ($batch) use ($syncLog) {
                $locationsCount = Location::count();
                $episodesCount = Episode::count();
                $charactersCount = Character::count();

                $syncLog->update([
                    'status' => 'completed',
                    'finished_at' => now(),
                    'locations_count' => $locationsCount,
                    'episodes_count' => $episodesCount,
                    'characters_count' => $charactersCount,
                ]);

                echo "\n";
                echo "==================================================\n";
                echo "  SYNC COMPLETED!\n";
                echo "==================================================\n";
                echo "  Locations:  {$locationsCount}\n";
                echo "  Episodes:   {$episodesCount}\n";
                echo "  Characters: {$charactersCount}\n";
                echo "==================================================\n\n";

                Log::channel('sync')->info("Sync batch completed: {$locationsCount} locations, {$episodesCount} episodes, {$charactersCount} characters");
            })
            ->catch(function ($batch, $e) use ($syncLog) {
                $syncLog->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'locations_count' => Location::count(),
                    'episodes_count' => Episode::count(),
                    'characters_count' => Character::count(),
                    'error_message' => $e->getMessage(),
                ]);

                echo "\n";
                echo "==================================================\n";
                echo "  SYNC FAILED!\n";
                echo "==================================================\n";
                echo "  Error: {$e->getMessage()}\n";
                echo "==================================================\n\n";

                Log::channel('sync')->error("Sync batch failed: {$e->getMessage()}");
            })
            ->finally(function ($batch) use ($syncLog) {
                if ($syncLog->fresh()->status === 'queued') {
                    $syncLog->update([
                        'status' => $batch->cancelled() ? 'cancelled' : 'failed',
                        'finished_at' => now(),
                        'locations_count' => Location::count(),
                        'episodes_count' => Episode::count(),
                        'characters_count' => Character::count(),
                    ]);
                }
            })
            ->onQueue('default')
            ->dispatch();

        $syncLog->update(['batch_id' => $batch->id]);

        echo "[Dispatcher] Batch ID: {$batch->id}\n";
        echo "[Dispatcher] Jobs encolados. Esperando procesamiento...\n\n";
    }
}
