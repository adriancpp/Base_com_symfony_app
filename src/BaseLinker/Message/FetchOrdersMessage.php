<?php

declare(strict_types=1);

namespace App\BaseLinker\Message;

/**
 * Message to fetch orders from BaseLinker (for a given date and optional filters).
 * Handled by FetchOrdersHandler; use sync or async transport.
 */
final class FetchOrdersMessage
{
    public function __construct(
        private readonly int $dateFromTimestamp,
        private readonly ?int $dateConfirmedFromTimestamp = null,
        private readonly ?string $orderSource = null,
        private readonly bool $getUnconfirmed = false,
    ) {
    }

    public function getDateFromTimestamp(): int
    {
        return $this->dateFromTimestamp;
    }

    public function getDateConfirmedFromTimestamp(): ?int
    {
        return $this->dateConfirmedFromTimestamp;
    }

    public function getOrderSource(): ?string
    {
        return $this->orderSource;
    }

    public function getGetUnconfirmed(): bool
    {
        return $this->getUnconfirmed;
    }
}
