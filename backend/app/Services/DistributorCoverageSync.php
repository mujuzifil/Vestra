<?php

namespace App\Services;

use App\Enums\DistributorAccountStatus;
use App\Models\Distributor;
use App\Models\DistributorBranch;
use App\Models\DistributorServiceArea;
use Illuminate\Support\Collection;

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
        'tororo' => [0.692999, 34.1809],
        'soroti' => [1.7145, 33.6111],
        'busia' => [0.4651, 34.0922],
        'iganga' => [0.6092, 33.4686],
        'masindi' => [1.6744, 31.7150],
        'bushenyi' => [-0.5425, 30.1850],
    ];

    public function sync(Distributor $distributor): Distributor
    {
        $distributor->refresh();

        $defaultBranch = $this->syncDefaultBranch($distributor);
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

    public function resolveMacroRegion(?string $district, ?string $city = null, ?string $fallback = null): string
    {
        foreach ([$district, $city, $fallback] as $candidate) {
            $normalized = $this->normalizeLabel($candidate);
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
        $fromPlusCode = $this->coordinatesFromPlusCode($address);
        if ($fromPlusCode !== null) {
            return $fromPlusCode;
        }

        foreach ([$district, $city] as $candidate) {
            $key = $this->normalizeLabel($candidate);
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

    private function syncDefaultBranch(Distributor $distributor): DistributorBranch
    {
        $branch = $distributor->branches()->where('is_default', true)->first()
            ?? $distributor->branches()->first();

        if ($branch === null) {
            $branch = DistributorBranch::create([
                'distributor_id' => $distributor->id,
                'name' => 'Head Office',
                'manager_name' => $distributor->primary_contact_name,
                'phone' => $distributor->phone,
                'email' => $distributor->email,
                'country' => $distributor->country ?: 'Uganda',
                'district' => $distributor->district,
                'city' => $distributor->city,
                'address' => $distributor->address,
                'status' => 'active',
                'is_default' => true,
            ]);
        } else {
            $branch->fill([
                'country' => $distributor->country ?: ($branch->country ?: 'Uganda'),
                'district' => $distributor->district ?: $branch->district,
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
        $district = trim((string) ($distributor->district ?: $branch->district ?: $distributor->city ?: $branch->city));
        if ($district === '') {
            return;
        }

        $region = $this->resolveMacroRegion(
            $distributor->district,
            $distributor->city,
            $branch->district
        );

        $exists = $distributor->serviceAreas()
            ->get()
            ->contains(function (DistributorServiceArea $area) use ($region, $district): bool {
                return mb_strtolower((string) $area->district) === mb_strtolower($district)
                    && $this->normalizeMacroRegion((string) $area->region) === $region;
            });

        if ($exists) {
            // Normalize any existing rows that used district-as-region naming.
            $distributor->serviceAreas()
                ->whereRaw('LOWER(district) = ?', [mb_strtolower($district)])
                ->each(function (DistributorServiceArea $area) use ($region): void {
                    if ($this->normalizeMacroRegion((string) $area->region) !== $region
                        || ! in_array($area->region, self::MACRO_REGIONS, true)
                    ) {
                        $area->update([
                            'region' => $region,
                            'status' => $area->status ?: 'covered',
                        ]);
                    }
                });

            return;
        }

        // Upgrade a single poorly-seeded area instead of duplicating noise.
        if ($distributor->serviceAreas()->count() === 1) {
            $only = $distributor->serviceAreas()->first();
            if ($only !== null) {
                $only->update([
                    'branch_id' => $branch->id,
                    'region' => $region,
                    'district' => $district,
                    'status' => 'covered',
                ]);

                return;
            }
        }

        DistributorServiceArea::create([
            'distributor_id' => $distributor->id,
            'branch_id' => $branch->id,
            'region' => $region,
            'district' => $district,
            'status' => 'covered',
        ]);
    }

    private function ensureBranchCoordinates(Distributor $distributor, DistributorBranch $branch): void
    {
        if ($branch->latitude !== null && $branch->longitude !== null) {
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

    /**
     * Minimal Open Location Code decoder for full plus codes (e.g. 8H4C+6JR).
     *
     * @return array{0: float, 1: float}|null
     */
    private function coordinatesFromPlusCode(?string $haystack): ?array
    {
        if (! filled($haystack)) {
            return null;
        }

        if (! preg_match('/\b([23456789CFGHJMPQRVWX]{2,8}\+[23456789CFGHJMPQRVWX]{2,3})\b/i', $haystack, $matches)) {
            return null;
        }

        $code = strtoupper($matches[1]);
        $alphabet = '23456789CFGHJMPQRVWX';
        [$area, $suffix] = explode('+', $code, 2);

        if (strlen($area) < 2 || strlen($suffix) < 2) {
            return null;
        }

        $decodePair = function (string $chars) use ($alphabet): array {
            $lat = 0.0;
            $lng = 0.0;
            $place = 1.0;
            for ($i = 0; $i + 1 < strlen($chars); $i += 2) {
                $place *= 20.0;
                $latIndex = strpos($alphabet, $chars[$i]);
                $lngIndex = strpos($alphabet, $chars[$i + 1]);
                if ($latIndex === false || $lngIndex === false) {
                    return [null, null];
                }
                $lat += $latIndex / $place;
                $lng += $lngIndex / $place;
            }

            return [$lat, $lng];
        };

        [$latFrac, $lngFrac] = $decodePair($area);
        if ($latFrac === null || $lngFrac === null) {
            return null;
        }

        // Refine with first two suffix chars when available.
        if (strlen($suffix) >= 2) {
            $place = 20 ** (int) (strlen($area) / 2);
            $latIndex = strpos($alphabet, $suffix[0]);
            $lngIndex = strpos($alphabet, $suffix[1]);
            if ($latIndex !== false && $lngIndex !== false) {
                $place *= 20.0;
                $latFrac += $latIndex / $place;
                $lngFrac += $lngIndex / $place;
            }
        }

        $latitude = $latFrac * 20.0 - 90.0;
        $longitude = $lngFrac * 20.0 - 180.0;

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        return [round($latitude, 6), round($longitude, 6)];
    }

    private function normalizeLabel(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }
}
