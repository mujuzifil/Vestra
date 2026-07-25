<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DistributorChannel;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckoutRequest;
use App\Http\Resources\V1\Distributor\OrderResource as DistributorOrderResource;
use App\Http\Resources\V1\OrderResource;
use App\Models\DistributorBranch;
use App\Services\DistributorOrderService;
use App\Services\OrderService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly OrderService $service,
        private readonly DistributorOrderService $distributorOrderService,
    ) {}

    public function store(CheckoutRequest $request): JsonResponse
    {
        $channel = $request->input('channel', DistributorChannel::RETAIL->value);

        if ($channel === DistributorChannel::DISTRIBUTOR->value) {
            return $this->storeDistributorOrder($request);
        }

        $order = $this->service->createOrder($request->user(), $request->validated());

        return $this->successResponse(
            new OrderResource($order),
            'Order placed successfully.',
            201
        );
    }

    private function storeDistributorOrder(CheckoutRequest $request): JsonResponse
    {
        $user = $request->user();
        $distributor = $user->distributor;

        if (! $distributor || ! $distributor->isActive()) {
            return $this->errorResponse('Distributor access required.', 403);
        }

        $branchId = $request->input('distributor_branch_id');

        if ($branchId) {
            $branch = DistributorBranch::where('distributor_id', $distributor->id)
                ->find($branchId);

            if (! $branch) {
                throw ValidationException::withMessages([
                    'distributor_branch_id' => ['The selected branch does not belong to your distributor account.'],
                ]);
            }
        }

        $order = $this->distributorOrderService->createOrder(
            $user,
            $distributor,
            $request->validated()
        );

        $message = $request->input('payment_method') === PaymentMethod::CREDIT->value
            ? 'Order placed on credit. Awaiting authorization.'
            : 'Order placed successfully.';

        return $this->successResponse(
            new DistributorOrderResource($order),
            $message,
            201
        );
    }
}
