<?php

namespace App\Console\Commands;

use App\Jobs\SyncCharactersJob;
use App\Jobs\SyncEpisodesJob;
use App\Jobs\SyncLocationsJob;
use App\Models\Character;
use App\Models\Episode;
use App\Models\Location;
use App\Models\SyncLog;
use App\Services\RickAndMorty\Client;
use Illuminate\Console\Command;

class SyncRickAndMorty extends Command
{
    protected $signature = 'sync:rick-and-morty';

    protected $description = 'Synchronize all Rick and Morty API data into the local database';

    public function handle(Client $client): int
    {
        $syncLog = SyncLog::create([
            'status' => 'running',
            'started_at' => now(),
        ]);

        $this->info("Iniciando sincronización Rick & Morty...");
        $this->info("SyncLog #{$syncLog->id}\n");

        try {
            $this->info("📍 Sincronizando locations...");
            (new SyncLocationsJob($syncLog))->handle($client);

            $this->info("📺 Sincronizando episodes...");
            (new SyncEpisodesJob($syncLog))->handle($client);

            $this->info("🧑 Sincronizando characters...");
            (new SyncCharactersJob($syncLog))->handle($client);

            $syncLog->update([
                'status' => 'completed',
                'finished_at' => now(),
            ]);

            $this->newLine();
            $this->info("==================================================");
            $this->info("  Sincronización completada!");
            $this->info("==================================================");
            $this->info("  Locations:  {$syncLog->locations_count}");
            $this->info("  Episodes:   {$syncLog->episodes_count}");
            $this->info("  Characters: {$syncLog->characters_count}");
            $this->info("==================================================\n");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $syncLog->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $e->getMessage(),
                'locations_count' => Location::count(),
                'episodes_count' => Episode::count(),
                'characters_count' => Character::count(),
            ]);

            $this->newLine();
            $this->error("==================================================");
            $this->error("  Sincronización FALLÓ!");
            $this->error("==================================================");
            $this->error("  Error: {$e->getMessage()}");
            $this->error("==================================================\n");

            return Command::FAILURE;
        }
    }
}
