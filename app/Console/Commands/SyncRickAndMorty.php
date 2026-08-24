<?php

namespace App\Console\Commands;

use App\Jobs\SyncDispatcherJob;
use Illuminate\Console\Command;

class SyncRickAndMorty extends Command
{
    protected $signature = 'sync:rick-and-morty';

    protected $description = 'Dispatch async synchronization of all Rick and Morty API data into the local database';

    public function handle(): int
    {
        SyncDispatcherJob::dispatch();

        $this->info('Sync job dispatched to queue.');
        $this->newLine();
        $this->info('The sync will run asynchronously. Monitor progress at:');
        $this->info('  - CLI: php artisan queue:work');
        $this->info('  - API: GET /api/sync/logs');

        return Command::SUCCESS;
    }
}
