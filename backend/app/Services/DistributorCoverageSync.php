<?php

namespace App\Services;

use App\Enums\DistributorAccountStatus;
use App\Models\Distributor;
use App\Models\DistributorBranch;
use App\Models\DistributorServiceArea;

class DistributorCoverageSync
{
    public const MACRO_REGIONS = ['Central', 'Eastern', 'Northern', 'Western'];

    /**
     * District (lowercase) => Uganda macro region.
     *
     * @var array<string, string>
     */
    private const DISTRICT_REGIONS = [
        'kampala' => 'Central',
        'wakiso' => 'Central',
        'mukono' => 'Central',
        'mpigi' => 'Central',
        'luweero' => 'Central',
        'luwero' => 'Central',
        'mityana' => 'Central',
        'mubende' => 'Central',
        'buikwe' => 'Central',
        'kayunga' => 'Central',
        'nakaseke' => 'Central',
        'nakasongola' => 'Central',
        'kiboga' => 'Central',
        'kyankwanzi' => 'Central',
        'gomba' => 'Central',
        'butambala' => 'Central',
        'kalungu' => 'Central',
        'masaka' => 'Central',
        'lwengo' => 'Central',
        'bukomansimbi' => 'Central',
        'kalangala' => 'Central',
        'lyantonde' => 'Central',
        'rakai' => 'Central',
        'kyotera' => 'Central',
        'ssembabule' => 'Central',
        'sembabule' => 'Central',
        'kitende' => 'Central',
        'entebbe' => 'Central',
        'nakawa' => 'Central',
        'jinja' => 'Eastern',
        'iganga' => 'Eastern',
        'kamuli' => 'Eastern',
        'mbale' => 'Eastern',
        'tororo' => 'Eastern',
        'soroti' => 'Eastern',
        'busia' => 'Eastern',
        'pallisa' => 'Eastern',
        'kumi' => 'Eastern',
        'kapchorwa' => 'Eastern',
        'sironko' => 'Eastern',
        'mayuge' => 'Eastern',
        'bugiri' => 'Eastern',
        'namutumba' => 'Eastern',
        'kaliro' => 'Eastern',
        'buyende' => 'Eastern',
        'luuka' => 'Eastern',
        'namayingo' => 'Eastern',
        'serere' => 'Eastern',
        'ngora' => 'Eastern',
        'bukedea' => 'Eastern',
        'bulambuli' => 'Eastern',
        'manafa' => 'Eastern',
        'bududa' => 'Eastern',
        'gulu' => 'Northern',
        'lira' => 'Northern',
        'arua' => 'Northern',
        'kitgum' => 'Northern',
        'pader' => 'Northern',
        'apac' => 'Northern',
        'nebbi' => 'Northern',
        'adjumani' => 'Northern',
        'moyo' => 'Northern',
        'yumbe' => 'Northern',
        'koboko' => 'Northern',
        'maracha' => 'Northern',
        'zombo' => 'Northern',
        'otuke' => 'Northern',
        'alebtong' => 'Northern',
        'dokolo' => 'Northern',
        'amolatar' => 'Northern',
        'oyam' => 'Northern',
        'kole' => 'Northern',
        'nwoya' => 'Northern',
        'amuru' => 'Northern',
        'lamwo' => 'Northern',
        'agago' => 'Northern',
        'omoro' => 'Northern',
        'pakwach' => 'Northern',
        'mbarara' => 'Western',
        'kabale' => 'Western',
        'fort portal' => 'Western',
        'kasese' => 'Western',
        'hoima' => 'Western',
        'masindi' => 'Western',
        'bushenyi' => 'Western',
        'ntungamo' => 'Western',
        'rukungiri' => 'Western',
        'kanungu' => 'Western',
        'kisoro' => 'Western',
        'ibanda' => 'Western',
        'isingiro' => 'Western',
        'kiruhura' => 'Western',
        'buhweju' => 'Western',
        'mitooma' => 'Western',
        'rubirizi' => 'Western',
        'sheema' => 'Western',
        'bunyangabu' => 'Western',
        'kyegegwa' => 'Western',
        'kyenjojo' => 'Western',
        'kamwenge' => 'Western',
        'bundibugyo' => 'Western',
        'ntoroko' => 'Western',
        'kikuube' => 'Western',
        'kakumiro' => 'Western',
        'kagadi' => 'Western',
        'kibale' => 'Western',
        'kibaale' => 'Western',
    ];

    /**
     * Approximate district centroids [lat, lng].
     *
     * @var array<string, array{0: float, 1: float}>
     */
    private const DISTRICT_CENTROIDS = [
        'kampala' => [0.3476, 32.5825],
        'wakiso' => [0.4044, 32.4590],
        'mukono' => [0.3533, 32.7553],
        'jinja' => [0.4244, 33.2041],
        'mbale' => [1.0821, 34.1750],
        'gulu' => [2.7746, 32.2990],
        'lira' => [2.2490, 32.8998],
        'arua' => [3.0201, 30.9111],
        'mbarara' => [-0.6072, 30.6545],
        'kabale' => [-1.2481, 29.9899],
        'fort portal' => [0.6710, 30.2747],
        'kasese' => [0.1833, 30.0833],
        'hoima' => [1.4356, 31.3436],
        'masaka' => [-0.3411, 31.7361],
        'entebbe' => [0.0512, 32.4637],
        'kitende' => [0.2250, 32.5200],
        'wakiso' => [0.4044, 32.4590],
        'entebbe' => [0.0512, 32.4637],
        'tororo' => [0.692999, 34.1809],
        'soroti' => [1.7145, 33.6111],
        'busia' => [0.4651, 34.0922],
        'iganga' => [0.6092, 33.4686],
        'masindi' => [1.6744, 31.7150],
        'bushenyi' => [-0.5425, 30.1850],
    ];

    /**
     * Locality / alias → canonical district label.
     *
     * @var array<string, string>
     */
    private const DISTRICT_ALIASES = [
        'kitende' => 'Wakiso',
        'entebbe' => 'Wakiso',
        'nakawa' => 'Kampala',
        'kololo' => 'Kampala',
        'makindye' => 'Kampala',
        'kawempe' => 'Kampala',
        'rubaga' => 'Kampala',
        'luwero' => 'Luweero',
        'sembabule' => 'Ssembabule',
        'kibaale' => 'Kibaale',
        'fort portal' => 'Fort Portal',
    ];

    /**
     * Values that must never appear as covered "districts".
     *
     * @var array<int, string>
     */
    private const INVALID_DISTRICTS = [
        'uganda',
        'east africa',
        'africa',
        'nationwide',
        'national',
        'all',
        'n/a',
        'na',
        'none',
        'central',
        'eastern',
        'northern',
        'western',
    ];

    public function sync(Distributor $distributor): Distributor
    {
        $distributor->refresh();

        $defaultBranch = $this->syncDefaultBranch($distributor);
        $this->pruneAndNormalizeServiceAreas($distributor, $defaultBranch);
        $this->ensurePrimaryServiceArea($distributor, $defaultBranch);
        $this->ensureBranchCoordinates($distributor, $defaultBranch);

        return $distributor->fresh(['branches', 'serviceAreas']);
    }

    public function backfillActivePartners(): int
    {
        $count = 0;

        Distributor::query()
            ->where('status', DistributorAccountStatus::ACTIVE->value)
            ->orderBy('id')
            ->each(function (Distributor $distributor) use (&$count): void {
                $this->sync($distributor);
                $count++;
            });

        return $count;
    }

    /**
     * Return a public-facing district label, or null when the value is not a real place.
     */
    public function canonicalizeDistrict(?string $value): ?string
    {
        $normalized = $this->normalizeLabel($value);
        if ($normalized === '' || in_array($normalized, self::INVALID_DISTRICTS, true)) {
            return null;
        }

        if (isset(self::DISTRICT_ALIASES[$normalized])) {
            return self::DISTRICT_ALIASES[$normalized];
        }

        if (isset(self::DISTRICT_REGIONS[$normalized])) {
            return mb_convert_case($normalized, MB_CASE_TITLE, 'UTF-8');
        }

        // Keep unknown but plausible place names; drop values that look like countries.
        if (preg_match('/\b(republic|kingdom|country)\b/i', (string) $value)) {
            return null;
        }

        return mb_convert_case(trim((string) $value), MB_CASE_TITLE, 'UTF-8');
    }

    public function resolveMacroRegion(?string $district, ?string $city = null, ?string $fallback = null): string
    {
        foreach ([$district, $city, $fallback] as $candidate) {
            $canonical = $this->canonicalizeDistrict($candidate) ?? $candidate;
            $normalized = $this->normalizeLabel($canonical);
            if ($normalized === '') {
                continue;
            }

            foreach (self::MACRO_REGIONS as $macro) {
                if ($normalized === mb_strtolower($macro)) {
                    return $macro;
                }
            }

            if (isset(self::DISTRICT_REGIONS[$normalized])) {
                return self::DISTRICT_REGIONS[$normalized];
            }

            if (isset(self::DISTRICT_ALIASES[$this->normalizeLabel($candidate)])) {
                $alias = self::DISTRICT_ALIASES[$this->normalizeLabel($candidate)];

                return self::DISTRICT_REGIONS[$this->normalizeLabel($alias)] ?? 'Central';
            }
        }

        return 'Central';
    }

    public function normalizeMacroRegion(string $region): string
    {
        $normalized = $this->normalizeLabel($region);

        foreach (self::MACRO_REGIONS as $macro) {
            if ($normalized === mb_strtolower($macro)) {
                return $macro;
            }
        }

        return $this->resolveMacroRegion($region);
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    public function resolveCoordinates(?string $address, ?string $district, ?string $city = null): ?array
    {
        foreach ([$district, $city] as $candidate) {
            $canonical = $this->canonicalizeDistrict($candidate);
            $key = $this->normalizeLabel($canonical ?? $candidate);
            if ($key !== '' && isset(self::DISTRICT_CENTROIDS[$key])) {
                return self::DISTRICT_CENTROIDS[$key];
            }
        }

        $region = $this->resolveMacroRegion($district, $city);
        $regionCentroids = [
            'Central' => [0.3476, 32.5825],
            'Eastern' => [1.0821, 34.1750],
            'Northern' => [2.7746, 32.2990],
            'Western' => [-0.6072, 30.6545],
        ];

        return $regionCentroids[$region] ?? null;
    }

    public function coordinatesAreInUganda(float $latitude, float $longitude): bool
    {
        return $latitude >= -1.6 && $latitude <= 4.3
            && $longitude >= 29.4 && $longitude <= 35.2;
    }

    private function pruneAndNormalizeServiceAreas(Distributor $distributor, DistributorBranch $branch): void
    {
        $seen = [];

        foreach ($distributor->serviceAreas()->get() as $area) {
            $canonical = $this->canonicalizeDistrict($area->district);
            if ($canonical === null) {
                $area->delete();

                continue;
            }

            $region = $this->resolveMacroRegion($canonical, null, $area->region);
            $key = mb_strtolower($region.'|'.$canonical);

            if (isset($seen[$key])) {
                $area->delete();

                continue;
            }

            $seen[$key] = true;
            $area->update([
                'branch_id' => $area->branch_id ?: $branch->id,
                'region' => $region,
                'district' => $canonical,
                'status' => $area->status ?: 'covered',
            ]);
        }
    }

    private function syncDefaultBranch(Distributor $distributor): DistributorBranch
    {
        $branch = $distributor->branches()->where('is_default', true)->first()
            ?? $distributor->branches()->first();

        $canonicalDistrict = $this->canonicalizeDistrict($distributor->district)
            ?: $this->canonicalizeDistrict($distributor->city);

        if ($branch === null) {
            $branch = DistributorBranch::create([
                'distributor_id' => $distributor->id,
                'name' => 'Head Office',
                'manager_name' => $distributor->primary_contact_name,
                'phone' => $distributor->phone,
                'email' => $distributor->email,
                'country' => $distributor->country ?: 'Uganda',
                'district' => $canonicalDistrict ?: $distributor->district,
                'city' => $distributor->city,
                'address' => $distributor->address,
                'status' => 'active',
                'is_default' => true,
            ]);
        } else {
            $branch->fill([
                'country' => $distributor->country ?: ($branch->country ?: 'Uganda'),
                'district' => $canonicalDistrict ?: ($distributor->district ?: $branch->district),
                'city' => $distributor->city ?: $branch->city,
                'address' => $distributor->address ?: $branch->address,
                'manager_name' => $branch->manager_name ?: $distributor->primary_contact_name,
                'phone' => $branch->phone ?: $distributor->phone,
                'email' => $branch->email ?: $distributor->email,
            ]);
            $branch->save();
        }

        return $branch->fresh();
    }

    private function ensurePrimaryServiceArea(Distributor $distributor, DistributorBranch $branch): void
    {
        $district = $this->canonicalizeDistrict($distributor->district)
            ?: $this->canonicalizeDistrict($branch->district)
            ?: $this->canonicalizeDistrict($distributor->city)
            ?: $this->canonicalizeDistrict($branch->city);

        if ($district === null) {
            return;
        }

        $region = $this->resolveMacroRegion($district);

        $exists = $distributor->serviceAreas()
            ->get()
            ->contains(function (DistributorServiceArea $area) use ($region, $district): bool {
                return mb_strtolower((string) $area->district) === mb_strtolower($district)
                    && $this->normalizeMacroRegion((string) $area->region) === $region;
            });

        if ($exists) {
            return;
        }

        if ($distributor->serviceAreas()->count() === 0) {
            DistributorServiceArea::create([
                'distributor_id' => $distributor->id,
                'branch_id' => $branch->id,
                'region' => $region,
                'district' => $district,
                'status' => 'covered',
            ]);
        }
    }

    private function ensureBranchCoordinates(Distributor $distributor, DistributorBranch $branch): void
    {
        $lat = $branch->latitude !== null ? (float) $branch->latitude : null;
        $lng = $branch->longitude !== null ? (float) $branch->longitude : null;

        if ($lat !== null && $lng !== null && $this->coordinatesAreInUganda($lat, $lng)) {
            return;
        }

        $coords = $this->resolveCoordinates(
            $distributor->address ?: $branch->address,
            $distributor->district ?: $branch->district,
            $distributor->city ?: $branch->city
        );

        if ($coords === null) {
            return;
        }

        $branch->update([
            'latitude' => $coords[0],
            'longitude' => $coords[1],
        ]);
    }

    private function normalizeLabel(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }
}
