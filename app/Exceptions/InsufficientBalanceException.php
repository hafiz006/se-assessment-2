<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientBalanceException extends RuntimeException
{
    public function __construct(string $message = 'Insufficient balance for this withdrawal.')
    {
        parent::__construct($message);
    }
}
