<?php

namespace App\Console\Commands;

use App\Services\CollectorService;
use Illuminate\Console\Command;

class MikrotikCollectCommand extends Command
{
    protected $signature = 'mikrotik:collect';
    protected $description = 'Poll active sessions from MikroTik RouterOS and compute delta usage accounting';

    public function handle(CollectorService $collector): int
    {
        $this->info('Starting MikroTik polling & accounting run...');
        $result = $collector->collect();

        if ($result['status'] === 'success') {
            $this->info("Successfully processed {$result['sessions_processed']} sessions. Run ID: {$result['collector_run_id']}");
            return Command::SUCCESS;
        }

        if ($result['status'] === 'skipped') {
            $this->warn("Skipped: {$result['message']}");
            return Command::SUCCESS;
        }

        $this->error("Collector failed: {$result['message']}");
        return Command::FAILURE;
    }
}
