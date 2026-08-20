<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\Episode;
use App\Models\Location;
use App\Models\SyncLog;
use App\Services\RickAndMorty\Client;
use App\Services\RickAndMorty\Helpers\UrlHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncRickAndMorty extends Command
{
    protected $signature = 'sync:rick-and-morty';

    protected $description = 'Synchronize all data from the Rick and Morty API into the local database';

    protected SyncLog $syncLog;

    public function handle(Client $client): int
    {
        $this->syncLog = SyncLog::create([
            'status' => 'running',
            'started_at' => now(),
        ]);

        $this->log('info', 'Sync started');
        $this->info('Starting Rick and Morty sync...');

        try {
            $this->syncLocations($client);
            $this->syncEpisodes($client);
            $this->syncCharacters($client);
        } catch (\Exception $e) {
            $this->log('error', "Sync failed: {$e->getMessage()}");
            $this->error("Sync failed: {$e->getMessage()}");

            $this->syncLog->update([
                'status' => 'failed',
                'finished_at' => now(),
                'locations_count' => Location::count(),
                'episodes_count' => Episode::count(),
                'characters_count' => Character::count(),
                'error_message' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }

        $locationsCount = Location::count();
        $episodesCount = Episode::count();
        $charactersCount = Character::count();

        $this->log('info', "Sync completed: {$locationsCount} locations, {$episodesCount} episodes, {$charactersCount} characters");
        $this->info('Sync completed!');
        $this->info("  Locations:  {$locationsCount}");
        $this->info("  Episodes:   {$episodesCount}");
        $this->info("  Characters: {$charactersCount}");

        $this->syncLog->update([
            'status' => 'completed',
            'finished_at' => now(),
            'locations_count' => $locationsCount,
            'episodes_count' => $episodesCount,
            'characters_count' => $charactersCount,
        ]);

        return Command::SUCCESS;
    }

    protected function syncLocations(Client $client): void
    {
        $this->log('info', 'Syncing locations');
        $this->info('Syncing locations...');

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

            $this->log('info', "Locations page {$page}/{$totalPages}: ".count($items).' synced');
            $this->line("  Page {$page}/{$totalPages}: ".count($items).' locations synced');

            $hasMorePages = $page < $totalPages;
            $page++;
        }

        $this->log('info', 'Locations sync completed', ['count' => Location::count()]);
    }

    protected function syncEpisodes(Client $client): void
    {
        $this->log('info', 'Syncing episodes');
        $this->info('Syncing episodes...');

        $page = 1;
        $hasMorePages = true;

        while ($hasMorePages) {
            $response = $client->getEpisodes($page);
            $items = $response['results'];
            $totalPages = $response['info']['pages'];

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

            $this->log('info', "Episodes page {$page}/{$totalPages}: ".count($items).' synced');
            $this->line("  Page {$page}/{$totalPages}: ".count($items).' episodes synced');

            $hasMorePages = $page < $totalPages;
            $page++;
        }

        $this->log('info', 'Episodes sync completed', ['count' => Episode::count()]);
    }

    protected function syncCharacters(Client $client): void
    {
        $this->log('info', 'Syncing characters');
        $this->info('Syncing characters...');

        $page = 1;
        $hasMorePages = true;

        while ($hasMorePages) {
            $response = $client->getCharacters($page);
            $items = $response['results'];
            $totalPages = $response['info']['pages'];

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

            $this->log('info', "Characters page {$page}/{$totalPages}: ".count($items).' synced');
            $this->line("  Page {$page}/{$totalPages}: ".count($items).' characters synced');

            $hasMorePages = $page < $totalPages;
            $page++;
        }

        $this->log('info', 'Characters sync completed', ['count' => Character::count()]);
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
