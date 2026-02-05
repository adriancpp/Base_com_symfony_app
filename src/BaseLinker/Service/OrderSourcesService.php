<?php

declare(strict_types=1);

namespace App\BaseLinker\Service;

use App\BaseLinker\Client\BaseLinkerClient;
use App\BaseLinker\Exception\BaseLinkerApiException;

/**
 * Fetches order sources (marketplaces) from BaseLinker.
 * Sources are grouped by type: allegro, ebay, amazon, shop, personal, etc.
 */
final class OrderSourcesService
{
    public function __construct(
        private readonly BaseLinkerClient $client
    ) {
    }

    /**
     * Get available order sources (marketplaces) from BaseLinker.
     *
     * @return array<string, array<string, string>> keys = marketplace type (allegro, ebay, etc.), values = [id => name]
     * @throws BaseLinkerApiException
     */
    public function getSources(): array
    {
        $response = $this->client->request('getOrderSources', []);

        $sources = $response['sources'] ?? [];

        if (!is_array($sources)) {
            return [];
        }

        return $sources;
    }

    /**
     * Get marketplace types (allegro, ebay, amazon, etc.) that have at least one account.
     *
     * @return list<string>
     */
    public function getMarketplaceTypes(): array
    {
        $sources = $this->getSources();
        $types = [];

        foreach ($sources as $type => $accounts) {
            if (!is_array($accounts) || empty($accounts)) {
                continue;
            }
            $types[] = $type;
        }

        return $types;
    }
}
