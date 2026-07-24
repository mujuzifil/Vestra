<?php

namespace App\Events\Notification;

use App\Models\Distributor;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StatementGenerated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Distributor $distributor,
        public string $statementPeriod
    ) {}
}
