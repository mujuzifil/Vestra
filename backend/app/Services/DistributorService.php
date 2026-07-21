<?php

namespace App\Services;

use App\Models\DistributorRequest;
use App\Repositories\DistributorRepository;

class DistributorService
{
    public function __construct(private readonly DistributorRepository $repository) {}

    public function submit(array $data): DistributorRequest
    {
        $request = $this->repository->create($data);
        $request->refresh();

        return $request;
    }
}
