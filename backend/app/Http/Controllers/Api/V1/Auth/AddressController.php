<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAddressRequest;
use App\Http\Resources\V1\AddressResource;
use App\Models\CustomerAddress;
use App\Services\AuditService;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()
            ->addresses()
            ->orderBy('is_default', 'desc')
            ->orderBy('is_default_shipping', 'desc')
            ->orderBy('is_default_billing', 'desc')
            ->get();

        return $this->successResponse(
            AddressResource::collection($addresses)
        );
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $address = CustomerAddress::make($data);
        $address->forceFill(['user_id' => $user->id])->save();

        if ($address->is_default) {
            $this->clearOtherDefaults($address, 'is_default');
        }

        if ($address->is_default_shipping) {
            $this->clearOtherDefaults($address, 'is_default_shipping');
        }

        if ($address->is_default_billing) {
            $this->clearOtherDefaults($address, 'is_default_billing');
        }

        AuditService::log(
            $user,
            'address_created',
            $address,
            ['source' => 'api'],
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new AddressResource($address->fresh()),
            'Address added successfully.',
            201
        );
    }

    public function show(Request $request, CustomerAddress $address): JsonResponse
    {
        $this->authorize('view', $address);

        return $this->successResponse(
            new AddressResource($address)
        );
    }

    public function update(StoreAddressRequest $request, CustomerAddress $address): JsonResponse
    {
        $this->authorize('update', $address);

        $data = $request->validated();

        $address->update($data);

        if ($this->flagChangedToTrue($address, $data, 'is_default')) {
            $this->clearOtherDefaults($address, 'is_default');
        }

        if ($this->flagChangedToTrue($address, $data, 'is_default_shipping')) {
            $this->clearOtherDefaults($address, 'is_default_shipping');
        }

        if ($this->flagChangedToTrue($address, $data, 'is_default_billing')) {
            $this->clearOtherDefaults($address, 'is_default_billing');
        }

        AuditService::log(
            $request->user(),
            'address_updated',
            $address,
            ['source' => 'api'],
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            new AddressResource($address->fresh()),
            'Address updated successfully.'
        );
    }

    public function destroy(Request $request, CustomerAddress $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $address->delete();

        AuditService::log(
            $request->user(),
            'address_deleted',
            null,
            ['address_id' => $address->id, 'source' => 'api'],
            $request->ip(),
            $request->userAgent()
        );

        return $this->successResponse(
            null,
            'Address deleted successfully.'
        );
    }

    private function clearOtherDefaults(CustomerAddress $address, string $column): void
    {
        $address->user
            ->addresses()
            ->where('id', '!=', $address->id)
            ->where($column, true)
            ->update([$column => false]);
    }

    private function flagChangedToTrue(CustomerAddress $address, array $data, string $column): bool
    {
        return ($data[$column] ?? false) === true;
    }
}
