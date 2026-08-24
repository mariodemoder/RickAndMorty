<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\SyncLog;
use App\Models\SyncRawResponse;
use App\Services\RickAndMorty\Client;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncEpisodesJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public int $timeout = 120;

    public function __construct(
        protected SyncLog $syncLog,
    ) {}

    public function handle(Client $client): void
    {
        echo "[Episodes] Iniciando sincronización de episodes...\n";

        $this->log('info', 'Syncing episodes');

        $page = 1;
        $hasMorePages = true;

        while ($hasMorePages) {
            $response = $client->getEpisodes($page);
            $items = $response['data']['results'];
            $totalPages = $response['data']['info']['pages'];

            SyncRawResponse::create([
                'sync_log_id' => $this->syncLog->id,
                'resource_type' => 'episode',
                'page_number' => $page,
                'total_pages' => $totalPages,
                'response_body' => gzcompress($response['raw'], 6),
                'items_count' => count($items),
            ]);

            DB::transaction(function () use ($items) {
                foreach ($items as $item) {
                    Episode::updateOrCreate(
                        ['external_id' => $item['id']],
                        [
                            'name' => $item['name'],
                            'air_date' => $item['air_date'],
                            'episode_code' => $item['episode'],
                        ]
                    );
                }
            });

            echo "[Episodes] Página {$page}/{$totalPages}: " . count($items) . " sincronizados\n";
            $this->log('info', "Episodes page {$page}/{$totalPages}: ".count($items).' synced');

            $hasMorePages = $page < $totalPages;
            $page++;
        }

        $count = Episode::count();
        echo "[Episodes] ✅ Completado: {$count} episodes totales\n\n";
        $this->log('info', 'Episodes sync completed', ['count' => $count]);

        $this->syncLog->update(['episodes_count' => $count]);
    }

    public function failed(\Throwable $exception): void
    {
        echo "[Episodes] ❌ Job falló: {$exception->getMessage()}\n";
        $this->log('error', "Episodes job failed: {$exception->getMessage()}");
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
