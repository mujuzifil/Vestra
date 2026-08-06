<?php

namespace App\Services;

use App\Enums\CompanyStatus;
use App\Models\CompanyProfile;
use App\Models\DistributorRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CompanyProfileService
{
    public function getOrCreateForUser(User $user): CompanyProfile
    {
        return CompanyProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'primary_contact_email' => $user->email,
                'primary_contact_name' => $user->name,
                'primary_contact_phone' => $user->phone,
            ]
        );
    }

    public function updateForUser(User $user, array $data): CompanyProfile
    {
        $profile = $this->getOrCreateForUser($user);
        $profile->update($data);

        return $profile;
    }

    /**
     * Resolve or create the canonical company profile for a quote submission.
     */
    public function resolveForQuote(?User $user, array $data): ?CompanyProfile
    {
        if ($user !== null) {
            $profile = $this->getOrCreateForUser($user);
            $this->syncQuoteFields($profile, $data);

            return $profile;
        }

        $email = $data['email'] ?? null;

        if (! filled($email)) {
            return null;
        }

        $existingUser = User::query()->where('email', $email)->first();

        if ($existingUser === null) {
            $existingUser = User::create([
                'name' => $data['full_name'] ?? explode('@', (string) $email)[0],
                'email' => $email,
                'phone' => $data['phone'] ?? null,
                'password' => bcrypt(str()->random(32)),
                'status' => 'active',
            ]);
        }

        $profile = $this->getOrCreateForUser($existingUser);
        $this->syncQuoteFields($profile, $data);

        return $profile;
    }

    /**
     * Ensure a company profile exists and is populated when a distributor is approved.
     */
    public function syncFromDistributorApproval(User $user, DistributorRequest $request): CompanyProfile
    {
        $profile = $this->getOrCreateForUser($user);

        $profile->fill([
            'company_name' => $request->company_name ?? $profile->company_name,
            'business_type' => $request->business_type ?? $profile->business_type,
            'primary_contact_name' => $request->contact_person ?? $profile->primary_contact_name,
            'primary_contact_email' => $request->email ?? $profile->primary_contact_email,
            'primary_contact_phone' => $request->phone ?? $profile->primary_contact_phone,
            'country' => $request->country ?? $profile->country ?? 'Uganda',
            'district' => $request->region ?? $profile->district,
            'address' => $request->address ?? $profile->address,
            'status' => CompanyStatus::ACTIVE->value,
        ])->save();

        return $profile;
    }

    /**
     * Authenticated quote submissions must resolve to a company profile.
     *
     * @throws ValidationException
     */
    public function requireForAuthenticatedUser(User $user, array $data): CompanyProfile
    {
        $profile = $this->resolveForQuote($user, $data);

        if ($profile === null) {
            throw ValidationException::withMessages([
                'company' => 'Unable to resolve company profile for this account.',
            ]);
        }

        return $profile;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncQuoteFields(CompanyProfile $profile, array $data): void
    {
        $updates = [];

        if (filled($data['company_name'] ?? null) && blank($profile->company_name)) {
            $updates['company_name'] = $data['company_name'];
        }

        if (filled($data['full_name'] ?? null) && blank($profile->primary_contact_name)) {
            $updates['primary_contact_name'] = $data['full_name'];
        }

        if (filled($data['email'] ?? null) && blank($profile->primary_contact_email)) {
            $updates['primary_contact_email'] = $data['email'];
        }

        if (filled($data['phone'] ?? null) && blank($profile->primary_contact_phone)) {
            $updates['primary_contact_phone'] = $data['phone'];
        }

        if (filled($data['district'] ?? null) && blank($profile->district)) {
            $updates['district'] = $data['district'];
        }

        if (filled($data['city'] ?? null) && blank($profile->city)) {
            $updates['city'] = $data['city'];
        }

        if (filled($data['address'] ?? null) && blank($profile->address)) {
            $updates['address'] = $data['address'];
        }

        if ($updates !== []) {
            $profile->update($updates);
        }
    }
}
