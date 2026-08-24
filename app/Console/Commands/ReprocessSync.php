<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\Episode;
use App\Models\Location;
use App\Models\SyncLog;
use App\Models\SyncRawResponse;
use App\Services\RickAndMorty\Helpers\UrlHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReprocessSync extends Command
{
    protected $signature = 'sync:reprocess {syncLogId} {--resource= : Resource type to reprocess (location, episode, character)}';

    protected $description = 'Reprocess stored raw JSON responses from a previous sync';

    public function handle(): int
    {
        $syncLogId = $this->argument('syncLogId');
        $resourceFilter = $this->option('resource');

        $originalSyncLog = SyncLog::find($syncLogId);

        if (! $originalSyncLog) {
            $this->error("SyncLog #{$syncLogId} not found.");

            return Command::FAILURE;
        }

        $rawResponses = $originalSyncLog->rawResponses()
            ->orderBy('resource_type')
            ->orderBy('page_number')
            ->get();

        if ($rawResponses->isEmpty()) {
            $this->error("No raw responses found for SyncLog #{$syncLogId}.");

            return Command::FAILURE;
        }

        if ($resourceFilter) {
            $rawResponses = $rawResponses->where('resource_type', $resourceFilter);

            if ($rawResponses->isEmpty()) {
                $this->error("No raw responses found for resource type '{$resourceFilter}'.");

                return Command::FAILURE;
            }
        }

        $this->info("Reprocessing SyncLog #{$syncLogId}...");
        $this->newLine();

        $newSyncLog = SyncLog::create([
            'status' => 'running',
            'started_at' => now(),
        ]);

        $counts = [
            'location' => 0,
            'episode' => 0,
            'character' => 0,
        ];

        $grouped = $rawResponses->groupBy('resource_type');

        $orderedTypes = ['location', 'episode', 'character'];

        foreach ($orderedTypes as $resourceType) {
            if (! $grouped->has($resourceType)) {
                continue;
            }

            $responses = $grouped[$resourceType];
            $this->info("Processing {$resourceType}s...");

            foreach ($responses as $rawResponse) {
                $data = $rawResponse->getDecompressedBody();

                if ($data === null) {
                    $this->warn("  Failed to decompress page {$rawResponse->page_number}");

                    continue;
                }

                $items = $data['results'] ?? [];

                DB::transaction(function () use ($resourceType, $items, &$counts) {
                    foreach ($items as $item) {
                        match ($resourceType) {
                            'location' => $this->processLocation($item),
                            'episode' => $this->processEpisode($item),
                            'character' => $this->processCharacter($item),
                        };
                        $counts[$resourceType]++;
                    }
                });

                $this->info("  Page {$rawResponse->page_number}/{$rawResponse->total_pages}: ".count($items).' items');
            }
        }

        $newSyncLog->update([
            'status' => 'completed',
            'finished_at' => now(),
            'locations_count' => $counts['location'],
            'episodes_count' => $counts['episode'],
            'characters_count' => $counts['character'],
        ]);

        $this->newLine();
        $this->info("Reprocessing completed!");
        $this->info("  Locations: {$counts['location']}");
        $this->info("  Episodes: {$counts['episode']}");
        $this->info("  Characters: {$counts['character']}");
        $this->info("  New SyncLog ID: {$newSyncLog->id}");

        return Command::SUCCESS;
    }

    protected function processLocation(array $item): void
    {
        Location::updateOrCreate(
            ['external_id' => $item['id']],
            [
                'name' => $item['name'],
                'type' => $item['type'],
                'dimension' => $item['dimension'],
            ]
        );
    }

    protected function processEpisode(array $item): void
    {
        Episode::updateOrCreate(
            ['external_id' => $item['id']],
            [
                'name' => $item['name'],
                'air_date' => $item['air_date'],
                'episode_code' => $item['episode'],
            ]
        );
    }

    protected function processCharacter(array $item): void
    {
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
}
