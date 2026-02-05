<?php

declare(strict_types=1);

namespace App\Tests\BaseLinker\MessageHandler;

use App\BaseLinker\Exception\BaseLinkerApiException;
use App\BaseLinker\Message\FetchOrdersMessage;
use App\BaseLinker\MessageHandler\FetchOrdersHandler;
use App\BaseLinker\Service\OrdersService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class FetchOrdersHandlerTest extends TestCase
{
    public function testHandlerCallsServiceAndLogsSuccess(): void
    {
        $ordersService = $this->createMock(OrdersService::class);
        $ordersService->expects(self::once())
            ->method('getOrders')
            ->with(
                self::callback(fn (\DateTimeInterface $d) => $d->getTimestamp() === 1000),
                null,
                'allegro',
                false
            )
            ->willReturn([['order_id' => 1]]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('info')
            ->with('BaseLinker: fetched orders', self::callback(function (array $ctx): bool {
                return $ctx['count'] === 1 && $ctx['order_source'] === 'allegro';
            }));

        $handler = new FetchOrdersHandler($ordersService, $logger);
        $message = new FetchOrdersMessage(1000, null, 'allegro', false);

        ($handler)($message);
    }

    public function testHandlerLogsAndRethrowsOnApiError(): void
    {
        $ordersService = $this->createMock(OrdersService::class);
        $ordersService->method('getOrders')->willThrowException(
            new BaseLinkerApiException('Invalid token', 'ERR_001')
        );

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with('BaseLinker: fetch orders failed', self::callback(function (array $ctx): bool {
                return $ctx['message'] === 'Invalid token' && $ctx['code'] === 'ERR_001';
            }));

        $handler = new FetchOrdersHandler($ordersService, $logger);
        $message = new FetchOrdersMessage(1000);

        $this->expectException(BaseLinkerApiException::class);
        $this->expectExceptionMessage('Invalid token');

        ($handler)($message);
    }
}
