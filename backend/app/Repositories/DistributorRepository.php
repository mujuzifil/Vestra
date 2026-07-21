<?php

namespace App\Repositories;

use App\Models\DistributorRequest;

class DistributorRepository
{
    public function __construct(private readonly DistributorRequest $model) {}

    public function create(array $data): DistributorRequest
    {
        return $this->model->create($data);
    }
}
