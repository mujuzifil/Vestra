<?php

namespace App\Repositories;

use App\Models\ContactMessage;

class ContactRepository
{
    public function __construct(private readonly ContactMessage $model) {}

    public function create(array $data): ContactMessage
    {
        return $this->model->create($data);
    }
}
