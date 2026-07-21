<?php

namespace App\Services\ExchangeToken\Exceptions;

class ExpiredExchangeTokenException extends ExchangeTokenException
{
    public function __construct()
    {
        parent::__construct('Exchange token has expired.');
    }
}
