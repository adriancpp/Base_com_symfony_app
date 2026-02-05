<?php

declare(strict_types=1);

namespace App\BaseLinker\Exception;

use RuntimeException;

final class BaseLinkerApiException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?string $errorCode = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }
}
