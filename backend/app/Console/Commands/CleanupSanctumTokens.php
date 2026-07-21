<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class CleanupSanctumTokens extends Command
{
    protected $signature = 'sanctum:cleanup-expired';

    protected $description = 'Remove expired Laravel Sanctum personal access tokens.';

    public function handle(): int
    {
        $pruned = PersonalAccessToken::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("Pruned {$pruned} expired Sanctum token(s).");

        return SymfonyCommand::SUCCESS;
    }
}
