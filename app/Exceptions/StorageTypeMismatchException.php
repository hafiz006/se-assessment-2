<?php

namespace App\Exceptions;

use RuntimeException;

class StorageTypeMismatchException extends RuntimeException
{
    public function __construct(string $message = 'Storage type is not permitted for this account type.')
    {
        parent::__construct($message);
    }
}
