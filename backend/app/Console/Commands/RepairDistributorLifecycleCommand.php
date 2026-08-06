<?php

namespace App\Console\Commands;

use App\Enums\DistributorStatus;
use App\Models\Distributor;
use App\Models\DistributorRequest;
use App\Services\DistributorOnboardingService;
use Illuminate\Console\Command;

class RepairDistributorLifecycleCommand extends Command
{
    protected $signature = 'distributors:repair-lifecycle';

    protected $description = 'Repair approved applications missing distributors and ensure baseline records exist';

    public function handle(DistributorOnboardingService $onboarding): int
    {
        $orphansRepaired = 0;
        $creditEnsured = 0;
        $branchesEnsured = 0;

        $orphanRequests = DistributorRequest::query()
            ->where('status', DistributorStatus::APPROVED->value)
            ->whereDoesntHave('distributor')
            ->get();

        foreach ($orphanRequests as $request) {
            $onboarding->approve($request);
            $orphansRepaired++;
        }

        $distributors = Distributor::query()->with(['creditAccount', 'branches', 'request'])->get();

        foreach ($distributors as $distributor) {
            $onboarding->repairDistributor($distributor);
            $distributor->refresh();

            if ($distributor->creditAccount()->exists()) {
                $creditEnsured++;
            }

            if ($distributor->branches()->exists()) {
                $branchesEnsured++;
            }
        }

        $this->info("Orphan applications repaired: {$orphansRepaired}");
        $this->info("Distributors with credit account: {$creditEnsured}/{$distributors->count()}");
        $this->info("Distributors with default branch: {$branchesEnsured}/{$distributors->count()}");

        return self::SUCCESS;
    }
}
