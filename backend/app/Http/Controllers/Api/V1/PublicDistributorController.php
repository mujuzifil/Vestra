<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DistributorAccountStatus;
use App\Enums\DistributorStockAvailability;
use App\Enums\DistributorTier;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PublicDistributorResource;
use App\Models\Distributor;
use App\Models\DistributorBranch;
use App\Models\DistributorServiceArea;
use App\Services\SettingService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicDistributorController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly SettingService $settings,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'tier' => ['nullable', 'string', Rule::in(array_column(DistributorTier::cases(), 'value'))],
            'stock_availability' => ['nullable', 'string', Rule::in(array_column(DistributorStockAvailability::cases(), 'value'))],
        ]);

        $query = Distributor::with(['defaultBranch', 'serviceAreas'])
            ->where('status', DistributorAccountStatus::ACTIVE->value);

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('trading_name', 'like', "%{$search}%")
                    ->orWhere('primary_contact_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('district')) {
            $district = (string) $request->string('district');
            $query->where(function ($q) use ($district) {
                $q->where('district', 'like', "%{$district}%")
                    ->orWhereHas('serviceAreas', fn ($area) => $area->where('district', 'like', "%{$district}%"))
                    ->orWhereHas('defaultBranch', fn ($branch) => $branch->where('district', 'like', "%{$district}%"));
            });
        }

        if ($request->filled('region')) {
            $region = (string) $request->string('region');
            $query->where(function ($q) use ($region) {
                $q->whereHas('serviceAreas', fn ($area) => $area->where('region', 'like', "%{$region}%"))
                    ->orWhere('district', 'like', "%{$region}%")
                    ->orWhereHas('defaultBranch', fn ($branch) => $branch->where('district', 'like', "%{$region}%"));
            });
        }

        $area = $request->filled('area')
            ? (string) $request->string('area')
            : ($request->filled('city') ? (string) $request->string('city') : null);

        if ($area !== null && $area !== '') {
            $query->where(function ($q) use ($area) {
                $q->where('city', 'like', "%{$area}%")
                    ->orWhereHas('defaultBranch', fn ($branch) => $branch->where('city', 'like', "%{$area}%"));
            });
        }

        if ($request->filled('tier')) {
            $query->where('tier', (string) $request->string('tier'));
        }

        if ($request->filled('stock_availability')) {
            $query->where('stock_availability', (string) $request->string('stock_availability'));
        }

        $distributors = $query->orderBy('company_name')->get();

        return $this->successResponse(
            PublicDistributorResource::collection($distributors)
        );
    }

    public function show(int $id): JsonResponse
    {
        $distributor = Distributor::with(['defaultBranch', 'serviceAreas'])
            ->where('status', DistributorAccountStatus::ACTIVE->value)
            ->findOrFail($id);

        return $this->successResponse(
            new PublicDistributorResource($distributor)
        );
    }

    public function stats(): JsonResponse
    {
        $activeDistributors = Distributor::where('status', DistributorAccountStatus::ACTIVE->value)->count();
        $branches = DistributorBranch::query()
            ->whereHas('distributor', fn ($q) => $q->where('status', DistributorAccountStatus::ACTIVE->value))
            ->count();
        $districtsServed = DistributorServiceArea::query()
            ->where('status', 'covered')
            ->whereHas('distributor', fn ($q) => $q->where('status', DistributorAccountStatus::ACTIVE->value))
            ->distinct('district')
            ->count('district');

        $commercialCustomers = (int) $this->settings->get('network_commercial_customers', '0');

        return $this->successResponse([
            'active_distributors' => $activeDistributors,
            'branches' => $branches,
            'districts_served' => $districtsServed,
            'commercial_customers' => $commercialCustomers,
        ]);
    }

    public function coverageRegions(): JsonResponse
    {
        $coverageSync = app(\App\Services\DistributorCoverageSync::class);

        $areas = DistributorServiceArea::query()
            ->select(['region', 'district', 'status'])
            ->selectRaw('COUNT(*) as count')
            ->whereHas('distributor', fn ($q) => $q->where('status', DistributorAccountStatus::ACTIVE->value))
            ->groupBy('region', 'district', 'status')
            ->orderBy('district')
            ->get();

        $buckets = [];
        foreach (\App\Services\DistributorCoverageSync::MACRO_REGIONS as $macro) {
            $buckets[$macro] = [];
        }

        foreach ($areas as $item) {
            $macro = $coverageSync->normalizeMacroRegion((string) $item->region);
            $key = mb_strtolower((string) $item->district).'|'.(string) $item->status;

            if (! isset($buckets[$macro][$key])) {
                $buckets[$macro][$key] = [
                    'district' => $item->district,
                    'status' => $item->status,
                    'count' => 0,
                ];
            }

            $buckets[$macro][$key]['count'] += (int) $item->count;
        }

        $payload = collect($buckets)
            ->map(fn (array $districts) => collect($districts)->sortBy('district')->values())
            ->filter(fn ($districts) => $districts->isNotEmpty());

        return $this->successResponse($payload);
    }
}
