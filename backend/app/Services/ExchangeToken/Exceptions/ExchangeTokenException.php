<?php

namespace App\Services\ExchangeToken\Exceptions;

abstract class ExchangeTokenException extends \RuntimeException
{
    public function __construct(string $message = '')
    {
        parent::__construct($message);
    }
}
