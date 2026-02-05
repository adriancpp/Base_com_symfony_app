<?php

declare(strict_types=1);

namespace App\BaseLinker\Service;

use App\BaseLinker\Client\BaseLinkerClient;
use App\BaseLinker\Exception\BaseLinkerApiException;

/**
 * Fetches orders from BaseLinker. Thin layer over the client – builds params, returns orders.
 * Each order has order_source (marketplace: allegro, ebay, etc.).
 */
final class OrdersService
{
    public function __construct(
        private readonly BaseLinkerClient $client
    ) {
    }

    /**
     * Fetch orders from BaseLinker.
     *
     * @return array<int, array<string, mixed>> list of orders (max 100 per call; paginate with date_confirmed_from)
     * @throws BaseLinkerApiException
     */
    public function getOrders(
        \DateTimeInterface $dateFrom,
        ?\DateTimeInterface $dateConfirmedFrom = null,
        ?string $orderSource = null,
        bool $getUnconfirmed = false
    ): array {
        $params = [
            'date_from' => $dateFrom->getTimestamp(),
            'get_unconfirmed_orders' => $getUnconfirmed,
        ];

        if ($dateConfirmedFrom !== null) {
            $params['date_confirmed_from'] = $dateConfirmedFrom->getTimestamp();
        }

        if ($orderSource !== null && $orderSource !== '') {
            $params['filter_order_source'] = $orderSource;
        }

        $response = $this->client->request('getOrders', $params);

        return $response['orders'] ?? [];
    }
}
