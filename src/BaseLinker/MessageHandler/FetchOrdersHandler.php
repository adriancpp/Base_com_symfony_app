<?php

declare(strict_types=1);

namespace App\BaseLinker\MessageHandler;

use App\BaseLinker\Exception\BaseLinkerApiException;
use App\BaseLinker\Message\FetchOrdersMessage;
use App\BaseLinker\Service\OrdersService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class FetchOrdersHandler
{
    public function __construct(
        private readonly OrdersService $ordersService,
        private readonly LoggerInterface $logger
    ) {
    }

    public function __invoke(FetchOrdersMessage $message): void
    {
        $dateFrom = new \DateTimeImmutable('@' . $message->getDateFromTimestamp());
        $dateConfirmedFrom = $message->getDateConfirmedFromTimestamp() !== null
            ? new \DateTimeImmutable('@' . $message->getDateConfirmedFromTimestamp())
            : null;

        try {
            $orders = $this->ordersService->getOrders(
                $dateFrom,
                $dateConfirmedFrom,
                $message->getOrderSource(),
                $message->getGetUnconfirmed()
            );

            $this->logger->info('BaseLinker: fetched orders', [
                'count' => count($orders),
                'date_from' => $dateFrom->format('Y-m-d'),
                'order_source' => $message->getOrderSource(),
            ]);
        } catch (BaseLinkerApiException $e) {
            $this->logger->error('BaseLinker: fetch orders failed', [
                'message' => $e->getMessage(),
                'code' => $e->getErrorCode(),
            ]);
            throw $e;
        }
    }
}
