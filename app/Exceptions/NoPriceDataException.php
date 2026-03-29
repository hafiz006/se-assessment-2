<?php

namespace App\Exceptions;

use RuntimeException;

class NoPriceDataException extends RuntimeException
{
    public function __construct(string $message = 'No metal price data is available.')
    {
        parent::__construct($message);
    }
}
