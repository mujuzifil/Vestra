<?php

namespace App\Console\Commands;

use App\Services\ExchangeToken\ExchangeTokenService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class CleanupExchangeTokens extends Command
{
    protected $signature = 'auth:cleanup-exchange-tokens';

    protected $description = 'Remove expired and already-used authentication exchange tokens.';

    public function __construct(private readonly ExchangeTokenService $exchangeTokenService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $pruned = $this->exchangeTokenService->pruneExpired();

        $this->info("Pruned {$pruned} expired exchange token(s).");

        return SymfonyCommand::SUCCESS;
    }
}
