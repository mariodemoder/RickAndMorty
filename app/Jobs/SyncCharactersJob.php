<?php

namespace App\Jobs;

use App\Models\Character;
use App\Models\Episode;
use App\Models\SyncLog;
use App\Models\SyncRawResponse;
use App\Services\RickAndMorty\Client;
use App\Services\RickAndMorty\Helpers\UrlHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncCharactersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public int $timeout = 300;

    public function __construct(
        protected SyncLog $syncLog,
    ) {}

    public function handle(Client $client): void
    {
        echo "[Characters] Iniciando sincronización de characters...\n";

        $this->log('info', 'Syncing characters');

        $page = 1;
        $hasMorePages = true;

        while ($hasMorePages) {
            $response = $client->getCharacters($page);
            $items = $response['data']['results'];
            $totalPages = $response['data']['info']['pages'];

            SyncRawResponse::create([
                'sync_log_id' => $this->syncLog->id,
                'resource_type' => 'character',
                'page_number' => $page,
                'total_pages' => $totalPages,
                'response_body' => gzcompress($response['raw'], 6),
                'items_count' => count($items),
            ]);

            DB::transaction(function () use ($items) {
                foreach ($items as $item) {
                    $character = Character::updateOrCreate(
                        ['external_id' => $item['id']],
                        [
                            'name' => $item['name'],
                            'status' => $item['status'],
                            'species' => $item['species'],
                            'type' => $item['type'] ?? '',
                            'gender' => $item['gender'],
                            'image' => $item['image'],
                            'origin_location_id' => UrlHelper::extractIdFromUrl($item['origin']['url'] ?? ''),
                            'current_location_id' => UrlHelper::extractIdFromUrl($item['location']['url'] ?? ''),
                        ]
                    );

                    $episodeIds = array_map(
                        fn ($url) => UrlHelper::extractIdFromUrl($url),
                        $item['episode'] ?? []
                    );
                    $episodeIds = array_filter($episodeIds);

                    $episodes = Episode::whereIn('external_id', $episodeIds)
                        ->pluck('id')
                        ->toArray();

                    $character->episodes()->sync($episodes);
                }
            });

            echo "[Characters] Página {$page}/{$totalPages}: " . count($items) . " sincronizados\n";
            $this->log('info', "Characters page {$page}/{$totalPages}: ".count($items).' synced');

            $hasMorePages = $page < $totalPages;
            $page++;

            if ($hasMorePages) {
                usleep((int) config('rick-and-morty.request_delay_ms', 200) * 1000);
            }
        }

        $count = Character::count();
        echo "[Characters] ✅ Completado: {$count} characters totales\n\n";
        $this->log('info', 'Characters sync completed', ['count' => $count]);

        $this->syncLog->update(['characters_count' => $count]);
    }

    public function failed(\Throwable $exception): void
    {
        echo "[Characters] ❌ Job falló: {$exception->getMessage()}\n";
        $this->log('error', "Characters job failed: {$exception->getMessage()}");
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
