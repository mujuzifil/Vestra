<?php

namespace App\Services;

use App\Enums\DistributorAccountStatus;
use App\Enums\DistributorStatus;
use App\Models\CreditAccount;
use App\Models\Distributor;
use App\Models\DistributorBranch;
use App\Models\DistributorContact;
use App\Models\DistributorRequest;
use App\Models\User;
use App\Notifications\DistributorApprovedNotification;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class DistributorOnboardingService
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    public function approve(DistributorRequest $request, ?User $admin = null): Distributor
    {
        return DB::transaction(function () use ($request, $admin) {
            $request->update(['status' => DistributorStatus::APPROVED]);

            $user = $this->resolveUser($request);

            $distributor = Distributor::create([
                'user_id' => $user->id,
                'distributor_request_id' => $request->id,
                'status' => DistributorAccountStatus::ACTIVE,
                'company_name' => $request->company_name,
                'trading_name' => $request->company_name,
                'business_type' => $request->business_type,
                'years_in_business' => $request->years_in_operation,
                'primary_contact_name' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'country' => $request->country,
                'district' => $request->region,
                'products_of_interest' => $request->products_interested_in,
                'expected_monthly_volume' => $request->estimated_volume,
                'approved_at' => now(),
            ]);

            $this->seedDefaultBranch($distributor, $request);
            $this->seedDefaultContact($distributor, $request);
            $this->seedCreditAccount($distributor);
            $this->assignDistributorRole($user);

            $user->notify(new DistributorApprovedNotification($distributor));

            $this->auditService::log(
                $admin ?? $user,
                'distributor_approved',
                $distributor,
                ['request_id' => $request->id, 'user_id' => $user->id],
                request()?->ip(),
                request()?->userAgent()
            );

            return $distributor->load(['branches', 'contacts', 'creditAccount']);
        });
    }

    public function reject(DistributorRequest $request, ?string $reason = null, ?User $admin = null): DistributorRequest
    {
        $request->update(['status' => DistributorStatus::REJECTED]);

        $this->auditService::log(
            $admin,
            'distributor_rejected',
            $request,
            ['reason' => $reason],
            request()?->ip(),
            request()?->userAgent()
        );

        return $request;
    }

    private function resolveUser(DistributorRequest $request): User
    {
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            $user = User::create([
                'name' => $request->contact_person,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt(uniqid('tmp_', true)),
                'status' => 'active',
            ]);
        }

        return $user;
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
            'name' => $request->contact_person,
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
