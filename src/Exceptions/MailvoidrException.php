<?php

namespace Mailvoidr\Laravel\Exceptions;

use Exception;

class MailvoidrException extends Exception
{
    /**
     * @param  array<string, array<int, string>|string>  $errors
     */
    public function __construct(
        string $message,
        public readonly int $statusCode = 0,
        public readonly array $errors = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
