<?php

namespace App\Jobs;

use App\Models\Location;
use App\Models\SyncLog;
use App\Services\RickAndMorty\Client;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncLocationsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public int $timeout = 180;

    public function __construct(
        protected SyncLog $syncLog,
    ) {}

    public function handle(Client $client): void
    {
        echo "[Locations] Iniciando sincronización de locations...\n";

        $this->log('info', 'Syncing locations');

        $page = 1;
        $hasMorePages = true;

        while ($hasMorePages) {
            $response = $client->getLocations($page);
            $items = $response['results'];
            $totalPages = $response['info']['pages'];

            DB::transaction(function () use ($items) {
                foreach ($items as $item) {
                    Location::updateOrCreate(
                        ['external_id' => $item['id']],
                        [
                            'name' => $item['name'],
                            'type' => $item['type'],
                            'dimension' => $item['dimension'],
                        ]
                    );
                }
            });

            echo "[Locations] Página {$page}/{$totalPages}: " . count($items) . " sincronizados\n";
            $this->log('info', "Locations page {$page}/{$totalPages}: ".count($items).' synced');

            $hasMorePages = $page < $totalPages;
            $page++;
        }

        $count = Location::count();
        echo "[Locations] ✅ Completado: {$count} locations totales\n\n";
        $this->log('info', 'Locations sync completed', ['count' => $count]);

        $this->syncLog->update(['locations_count' => $count]);
    }

    public function failed(\Throwable $exception): void
    {
        echo "[Locations] ❌ Job falló: {$exception->getMessage()}\n";
        $this->log('error', "Locations job failed: {$exception->getMessage()}");
    }

    public function retryUntil(): \DateTime
    {
        return now()->addMinutes(10);
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        Log::channel('sync')->$level($message, $context);

        $this->syncLog->entries()->create([
            'level' => $level,
            'message' => $message,
            'context' => $context ?: null,
            'created_at' => now(),
        ]);
    }
}
