<?php

namespace App\Console\Commands;

use App\Services\FupManagementService;
use Illuminate\Console\Command;

class MikrotikFupResetCommand extends Command
{
    protected $signature = 'mikrotik:fup-reset {--cycle=daily : FUP reset cycle (daily, monthly)}';
    protected $description = 'Reset FUP usage counters and restore normal bandwidth rate limits on MikroTik';

    public function handle(FupManagementService $fupService): int
    {
        $cycle = (string) $this->option('cycle');
        $this->info("Running FUP reset for cycle: {$cycle}...");

        $count = $fupService->resetFupCycle($cycle);
        $this->info("Successfully reset FUP for {$count} user(s).");

        return Command::SUCCESS;
    }
}
