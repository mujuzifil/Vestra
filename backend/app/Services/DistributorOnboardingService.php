<?php

namespace App\Services;

use App\Enums\DistributorAccountStatus;
use App\Enums\DistributorStatus;
use App\Events\Notification\DistributorApplicationApproved;
use App\Events\Notification\DistributorApplicationRejected;
use App\Models\CreditAccount;
use App\Models\Distributor;
use App\Models\DistributorBranch;
use App\Models\DistributorContact;
use App\Models\DistributorRequest;
use App\Models\DistributorServiceArea;
use App\Models\User;
use App\Notifications\DistributorApprovedNotification;
use App\Services\Catalog\CatalogSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class DistributorOnboardingService
{
    public function __construct(
        private readonly CatalogSyncService $catalogSync,
    ) {}

    public function approve(DistributorRequest $request, ?User $admin = null): Distributor
    {
        return DB::transaction(function () use ($request, $admin) {
            $request->refresh();

            if ($request->status === DistributorStatus::REJECTED) {
                throw ValidationException::withMessages([
                    'application' => 'Rejected applications cannot be approved.',
                ]);
            }

            $existingForRequest = Distributor::query()
                ->where('distributor_request_id', $request->id)
                ->first();

            if ($existingForRequest !== null) {
                if ($request->status !== DistributorStatus::APPROVED) {
                    $request->update(['status' => DistributorStatus::APPROVED]);
                }

                $this->ensureDistributorBaselines($existingForRequest, $request);
                $this->assignDistributorRole($existingForRequest->user);

                $this->logApproval($admin ?? $existingForRequest->user, $existingForRequest, $request, recovered: false);
                $this->schedulePostApprovalSideEffects($existingForRequest, notify: false);

                return $existingForRequest->load(['branches', 'contacts', 'creditAccount', 'serviceAreas']);
            }

            $orphanRecovery = $request->status === DistributorStatus::APPROVED;

            if ($request->status !== DistributorStatus::APPROVED) {
                $request->update(['status' => DistributorStatus::APPROVED]);
            }

            $user = $this->resolveUser($request);

            $existingForUser = Distributor::query()
                ->where('user_id', $user->id)
                ->where('distributor_request_id', '!=', $request->id)
                ->first();

            if ($existingForUser !== null) {
                throw ValidationException::withMessages([
                    'application' => 'A distributor account already exists for this applicant email.',
                ]);
            }

            $distributor = Distributor::create([
                'user_id' => $user->id,
                'distributor_request_id' => $request->id,
                'status' => DistributorAccountStatus::ACTIVE,
                'company_name' => $request->company_name,
                'trading_name' => $request->company_name,
                'business_type' => $request->business_type,
                'years_in_business' => $this->normalizeYearsInBusiness($request->years_in_operation),
                'primary_contact_name' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'country' => $request->country,
                'district' => $request->region,
                'products_of_interest' => is_array($request->products_interested_in)
                    ? implode(', ', $request->products_interested_in)
                    : $request->products_interested_in,
                'expected_monthly_volume' => $request->estimated_volume,
                'approved_at' => now(),
            ]);

            $this->ensureDistributorBaselines($distributor, $request);
            $this->assignDistributorRole($user);

            $this->logApproval($admin ?? $user, $distributor, $request, recovered: $orphanRecovery);
            $this->schedulePostApprovalSideEffects($distributor, notify: true);

            return $distributor->load(['branches', 'contacts', 'creditAccount', 'serviceAreas']);
        });
    }

    public function reject(DistributorRequest $request, ?string $reason = null, ?User $admin = null): DistributorRequest
    {
        return DB::transaction(function () use ($request, $reason, $admin) {
            $request->refresh();

            if ($request->status === DistributorStatus::APPROVED) {
                throw ValidationException::withMessages([
                    'application' => 'Approved applications cannot be rejected.',
                ]);
            }

            if ($request->status === DistributorStatus::REJECTED) {
                return $request;
            }

            $request->update([
                'status' => DistributorStatus::REJECTED,
                'rejection_reason' => $reason,
            ]);

            AuditService::log(
                $admin,
                'distributor_rejected',
                $request,
                ['reason' => $reason],
                request()?->ip(),
                request()?->userAgent()
            );

            $requestId = $request->id;

            DB::afterCommit(function () use ($requestId, $reason): void {
                $fresh = DistributorRequest::query()->find($requestId);

                if ($fresh === null) {
                    return;
                }

                event(new DistributorApplicationRejected($fresh, $reason));
            });

            return $request;
        });
    }

    public function markUnderReview(DistributorRequest $request, ?User $admin = null): DistributorRequest
    {
        return DB::transaction(function () use ($request, $admin) {
            $request->refresh();

            if (in_array($request->status, [DistributorStatus::APPROVED, DistributorStatus::REJECTED], true)) {
                throw ValidationException::withMessages([
                    'application' => 'Finalized applications cannot be returned to review.',
                ]);
            }

            if ($request->status === DistributorStatus::UNDER_REVIEW) {
                return $request;
            }

            $request->update(['status' => DistributorStatus::UNDER_REVIEW]);

            AuditService::log(
                $admin,
                'distributor_under_review',
                $request,
                null,
                request()?->ip(),
                request()?->userAgent()
            );

            return $request;
        });
    }

    public function requestInformation(DistributorRequest $request, ?string $notes = null, ?User $admin = null): DistributorRequest
    {
        return DB::transaction(function () use ($request, $notes, $admin) {
            $request->refresh();

            if (in_array($request->status, [DistributorStatus::APPROVED, DistributorStatus::REJECTED], true)) {
                throw ValidationException::withMessages([
                    'application' => 'Finalized applications cannot have information requested.',
                ]);
            }

            if ($request->status === DistributorStatus::INFORMATION_REQUESTED && blank($notes)) {
                return $request;
            }

            $request->update([
                'status' => DistributorStatus::INFORMATION_REQUESTED,
                'information_request_notes' => $notes ?? $request->information_request_notes,
            ]);

            AuditService::log(
                $admin,
                'distributor_information_requested',
                $request,
                ['notes' => $notes],
                request()?->ip(),
                request()?->userAgent()
            );

            return $request;
        });
    }

    public function repairDistributor(Distributor $distributor): void
    {
        $distributor->loadMissing('request');

        if ($distributor->request !== null) {
            $this->ensureDistributorBaselines($distributor, $distributor->request);

            return;
        }

        $this->seedCreditAccount($distributor);

        if (! $distributor->branches()->exists()) {
            DistributorBranch::create([
                'distributor_id' => $distributor->id,
                'name' => 'Head Office',
                'manager_name' => $distributor->primary_contact_name,
                'phone' => $distributor->phone,
                'email' => $distributor->email,
                'country' => $distributor->country,
                'district' => $distributor->district,
                'address' => $distributor->address,
                'is_default' => true,
                'status' => 'active',
            ]);
        }
    }

    public function ensureDistributorBaselines(Distributor $distributor, DistributorRequest $request): void
    {
        $this->seedDefaultBranch($distributor, $request);
        $this->seedDefaultContact($distributor, $request);
        $this->seedCreditAccount($distributor);
        $this->seedServiceAreas($distributor, $request);
    }

    private function resolveUser(DistributorRequest $request): User
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $request->contact_person ?: $request->company_name ?: 'Distributor',
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt(uniqid('tmp_', true)),
                'status' => 'active',
            ]);
        }

        return $user;
    }

    private function normalizeYearsInBusiness(?int $years): ?int
    {
        if ($years === null) {
            return null;
        }

        return max(0, min(255, $years));
    }

    private function seedDefaultBranch(Distributor $distributor, DistributorRequest $request): void
    {
        if ($distributor->branches()->exists()) {
            return;
        }

        DistributorBranch::create([
            'distributor_id' => $distributor->id,
            'name' => 'Head Office',
            'manager_name' => $request->contact_person,
            'phone' => $request->phone,
            'email' => $request->email,
            'country' => $request->country,
            'district' => $request->region,
            'address' => $request->address,
            'is_default' => true,
            'status' => 'active',
        ]);
    }

    private function seedDefaultContact(Distributor $distributor, DistributorRequest $request): void
    {
        if ($distributor->contacts()->exists()) {
            return;
        }

        DistributorContact::create([
            'distributor_id' => $distributor->id,
            'name' => $request->contact_person ?: 'Primary Contact',
            'role' => 'Primary Contact',
            'phone' => $request->phone,
            'email' => $request->email,
            'is_primary' => true,
            'permissions_json' => ['orders', 'quotes', 'invoices', 'payments'],
        ]);
    }

    private function seedCreditAccount(Distributor $distributor): void
    {
        if ($distributor->creditAccount()->exists()) {
            return;
        }

        CreditAccount::create([
            'distributor_id' => $distributor->id,
            'limit' => 0,
            'balance' => 0,
            'authorized_amount' => 0,
            'status' => 'pending',
        ]);
    }

    private function seedServiceAreas(Distributor $distributor, DistributorRequest $request): void
    {
        if ($distributor->serviceAreas()->exists()) {
            return;
        }

        $defaultBranch = $distributor->branches()->where('is_default', true)->first()
            ?? $distributor->branches()->first();

        $areas = collect([
            ['region' => $request->region, 'district' => $request->region],
            ['region' => $request->target_region, 'district' => $request->target_region],
            ['region' => $request->country, 'district' => $request->country],
        ])->filter(fn (array $area) => filled($area['region']) && filled($area['district']))
            ->unique(fn (array $area) => mb_strtolower($area['region'].'|'.$area['district']));

        foreach ($areas as $area) {
            DistributorServiceArea::create([
                'distributor_id' => $distributor->id,
                'branch_id' => $defaultBranch?->id,
                'region' => $area['region'],
                'district' => $area['district'],
                'status' => 'covered',
            ]);
        }
    }

    private function assignDistributorRole(User $user): void
    {
        $role = Role::firstOrCreate(['name' => 'distributor', 'guard_name' => 'web']);

        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }
    }

    private function logApproval(User $actor, Distributor $distributor, DistributorRequest $request, bool $recovered): void
    {
        AuditService::log(
            $actor,
            $recovered ? 'distributor_approved_recovered' : 'distributor_approved',
            $distributor,
            ['request_id' => $request->id, 'user_id' => $distributor->user_id],
            request()?->ip(),
            request()?->userAgent()
        );
    }

    private function schedulePostApprovalSideEffects(Distributor $distributor, bool $notify): void
    {
        $distributorId = $distributor->id;
        $userId = $distributor->user_id;

        DB::afterCommit(function () use ($distributorId, $userId, $notify): void {
            $this->catalogSync->syncDistributors();

            if (! $notify) {
                return;
            }

            $distributor = Distributor::query()->with('user')->find($distributorId);
            $user = User::query()->find($userId);

            if ($distributor === null || $user === null) {
                return;
            }

            $user->notify(new DistributorApprovedNotification($distributor));
            event(new DistributorApplicationApproved($distributor));
        });
    }
}
