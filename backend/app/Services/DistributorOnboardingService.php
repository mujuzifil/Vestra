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
use App\Models\User;
use App\Notifications\DistributorApprovedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class DistributorOnboardingService
{
    public function approve(DistributorRequest $request, ?User $admin = null): Distributor
    {
        return DB::transaction(function () use ($request, $admin) {
            $request->refresh();

            $existingForRequest = Distributor::query()
                ->where('distributor_request_id', $request->id)
                ->first();

            if ($existingForRequest !== null) {
                if ($request->status !== DistributorStatus::APPROVED) {
                    $request->update(['status' => DistributorStatus::APPROVED]);
                }

                return $existingForRequest->load(['branches', 'contacts', 'creditAccount']);
            }

            if ($request->status === DistributorStatus::REJECTED) {
                throw ValidationException::withMessages([
                    'application' => 'Rejected applications cannot be approved.',
                ]);
            }

            if ($request->status === DistributorStatus::APPROVED) {
                throw ValidationException::withMessages([
                    'application' => 'This application is marked approved but no distributor record exists. Retry approval.',
                ]);
            }

            $request->update(['status' => DistributorStatus::APPROVED]);

            $user = $this->resolveUser($request);

            $existingForUser = Distributor::query()
                ->where('user_id', $user->id)
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

            $this->seedDefaultBranch($distributor, $request);
            $this->seedDefaultContact($distributor, $request);
            $this->seedCreditAccount($distributor);
            $this->assignDistributorRole($user);

            AuditService::log(
                $admin ?? $user,
                'distributor_approved',
                $distributor,
                ['request_id' => $request->id, 'user_id' => $user->id],
                request()?->ip(),
                request()?->userAgent()
            );

            $distributorId = $distributor->id;
            $userId = $user->id;

            DB::afterCommit(function () use ($distributorId, $userId): void {
                $distributor = Distributor::query()->with('user')->find($distributorId);
                $user = User::query()->find($userId);

                if ($distributor === null || $user === null) {
                    return;
                }

                $user->notify(new DistributorApprovedNotification($distributor));
                event(new DistributorApplicationApproved($distributor));
            });

            return $distributor->load(['branches', 'contacts', 'creditAccount']);
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

            $request->update(['status' => DistributorStatus::REJECTED]);

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
        CreditAccount::create([
            'distributor_id' => $distributor->id,
            'limit' => 0,
            'balance' => 0,
            'authorized_amount' => 0,
            'status' => 'pending',
        ]);
    }

    private function assignDistributorRole(User $user): void
    {
        $role = Role::firstOrCreate(['name' => 'distributor', 'guard_name' => 'web']);
        $user->assignRole($role);
    }
}
