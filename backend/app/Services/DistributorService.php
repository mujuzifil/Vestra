<?php

namespace App\Services;

use App\Models\DistributorRequest;
use App\Repositories\DistributorRepository;
use Illuminate\Http\UploadedFile;

class DistributorService
{
    public function __construct(private readonly DistributorRepository $repository) {}

    public function submit(array $data): DistributorRequest
    {
        $documents = $data['documents'] ?? null;
        unset($data['documents']);

        $request = $this->repository->create($this->mapPayload($data));

        if (is_array($documents) && count($documents) > 0) {
            $stored = [];
            /** @var UploadedFile $file */
            foreach ($documents as $file) {
                $stored[] = $file->store("distributor_documents/{$request->id}", 'public');
            }
            $request->documents = $stored;
            $request->save();
        }

        $request->refresh();

        return $request;
    }

    private function mapPayload(array $data): array
    {
        $description = [];

        if (! empty($data['additional_information'])) {
            $description[] = $data['additional_information'];
        }

        if (! empty($data['position'])) {
            $description[] = 'Position: ' . $data['position'];
        }

        if (! empty($data['existing_brands'])) {
            $description[] = 'Existing brands: ' . $data['existing_brands'];
        }

        if (! empty($data['warehouse_availability'])) {
            $description[] = 'Warehouse availability: ' . $data['warehouse_availability'];
        }

        if (! empty($data['delivery_capability'])) {
            $description[] = 'Delivery capability: ' . $data['delivery_capability'];
        }

        return [
            'company_name' => $data['company_name'],
            'contact_person' => $data['contact_person'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['physical_address'] ?? null,
            'country' => $data['country'] ?? 'Uganda',
            'region' => $data['district'] ?? null,
            'business_type' => $data['business_type'],
            'years_in_operation' => isset($data['years_in_business']) ? (int) $data['years_in_business'] : null,
            'target_region' => $data['regions_covered'] ?? null,
            'business_description' => implode("\n\n", $description) ?: null,
        ];
    }
}
