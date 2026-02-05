<?php

declare(strict_types=1);

namespace App\Tests\BaseLinker\Service;

use App\BaseLinker\Client\BaseLinkerClient;
use App\BaseLinker\Exception\BaseLinkerApiException;
use App\BaseLinker\Service\OrdersService;
use PHPUnit\Framework\TestCase;

final class OrdersServiceTest extends TestCase
{
    public function testGetOrdersReturnsNormalizedList(): void
    {
        $client = $this->createMock(BaseLinkerClient::class);
        $client->method('request')
            ->with('getOrders', self::callback(function (array $params): bool {
                return $params['date_from'] === 1000
                    && $params['get_unconfirmed_orders'] === false
                    && ($params['filter_order_source'] ?? null) === 'allegro';
            }))
            ->willReturn([
                'status' => 'SUCCESS',
                'orders' => [
                    ['order_id' => 1, 'order_source' => 'allegro'],
                    ['order_id' => 2, 'order_source' => 'allegro'],
                ],
            ]);

        $service = new OrdersService($client);
        $dateFrom = new \DateTimeImmutable('@1000');

        $orders = $service->getOrders($dateFrom, orderSource: 'allegro');

        self::assertCount(2, $orders);
        self::assertSame(1, $orders[0]['order_id']);
        self::assertSame(2, $orders[1]['order_id']);
    }

    public function testGetOrdersNormalizesObjectFormat(): void
    {
        $client = $this->createMock(BaseLinkerClient::class);
        $client->method('request')->willReturn([
            'status' => 'SUCCESS',
            'orders' => [
                '12345' => ['order_id' => 12345, 'order_source' => 'ebay'],
                '12346' => ['order_id' => 12346, 'order_source' => 'ebay'],
            ],
        ]);

        $service = new OrdersService($client);
        $dateFrom = new \DateTimeImmutable('@2000');

        $orders = $service->getOrders($dateFrom);

        self::assertCount(2, $orders);
        self::assertSame(12345, $orders[0]['order_id']);
        self::assertSame(12346, $orders[1]['order_id']);
    }

    public function testGetOrdersPassesDateConfirmedFrom(): void
    {
        $client = $this->createMock(BaseLinkerClient::class);
        $client->method('request')
            ->with('getOrders', self::callback(function (array $params): bool {
                return isset($params['date_confirmed_from']) && $params['date_confirmed_from'] === 3000;
            }))
            ->willReturn(['status' => 'SUCCESS', 'orders' => []]);

        $service = new OrdersService($client);
        $dateFrom = new \DateTimeImmutable('@1000');
        $dateConfirmedFrom = new \DateTimeImmutable('@3000');

        $service->getOrders($dateFrom, dateConfirmedFrom: $dateConfirmedFrom);
    }

    public function testGetOrdersPropagatesApiException(): void
    {
        $client = $this->createMock(BaseLinkerClient::class);
        $client->method('request')->willThrowException(new BaseLinkerApiException('API down'));

        $service = new OrdersService($client);
        $dateFrom = new \DateTimeImmutable('@1000');

        $this->expectException(BaseLinkerApiException::class);
        $this->expectExceptionMessage('API down');

        $service->getOrders($dateFrom);
    }
}
