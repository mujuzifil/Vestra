<?php

namespace App\Services\ExchangeToken\Exceptions;

class InvalidExchangeTokenException extends ExchangeTokenException
{
    public function __construct()
    {
        parent::__construct('Invalid exchange token.');
    }
}
