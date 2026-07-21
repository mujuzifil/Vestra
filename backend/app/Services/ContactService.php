<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Repositories\ContactRepository;

class ContactService
{
    public function __construct(private readonly ContactRepository $repository) {}

    public function submit(array $data): ContactMessage
    {
        $message = $this->repository->create($data);
        $message->refresh();

        return $message;
    }
}
