<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DistributorAccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PublicDistributorResource;
use App\Models\Distributor;
use App\Models\DistributorBranch;
use App\Models\DistributorServiceArea;
use App\Services\SettingService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicDistributorController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly SettingService $settings,
    ) {}

    public function index(Request $request): JsonResponse
    {
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
        $areas = DistributorServiceArea::query()
            ->selectRaw('region, district, status, COUNT(*) as count')
            ->whereHas('distributor', fn ($q) => $q->where('status', DistributorAccountStatus::ACTIVE->value))
            ->groupBy('region', 'district', 'status')
            ->orderBy('region')
            ->orderBy('district')
            ->get();

        $grouped = $areas->groupBy('region')->map(function ($districts) {
            return $districts->map(function ($item) {
                return [
                    'district' => $item->district,
                    'status' => $item->status,
                    'count' => $item->count,
                ];
            })->values();
        });

        return $this->successResponse($grouped);
    }
}
