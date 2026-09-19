<?php

namespace App\Exceptions;

use RuntimeException;

class RdashException extends RuntimeException
{
    public function __construct(string $message, public readonly ?int $status = null, public readonly ?string $body = null)
    {
        parent::__construct($message);
    }
}
