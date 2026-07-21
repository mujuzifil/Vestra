<?php

namespace App\Services\ExchangeToken\Exceptions;

class UsedExchangeTokenException extends ExchangeTokenException
{
    public function __construct()
    {
        parent::__construct('Exchange token has already been used.');
    }
}
