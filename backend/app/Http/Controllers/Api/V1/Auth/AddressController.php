<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAddressRequest;
use App\Http\Resources\V1\AddressResource;
use App\Models\CustomerAddress;
use App\Traits\RespondsWithJson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()->orderBy('is_default', 'desc')->get();

        return $this->successResponse(
            AddressResource::collection($addresses)
        );
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        $data = $request->validated();

        // If this is the first address or marked as default, unset others
        if ($data['is_default'] ?? false) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address = CustomerAddress::make($data);
        $address->forceFill(['user_id' => $request->user()->id])->save();

        return $this->successResponse(
            new AddressResource($address),
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

        if (($data['is_default'] ?? false) && ! $address->is_default) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($data);

        return $this->successResponse(
            new AddressResource($address->fresh()),
            'Address updated successfully.'
        );
    }

    public function destroy(Request $request, CustomerAddress $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $address->delete();

        return $this->successResponse(
            null,
            'Address deleted successfully.'
        );
    }
}
