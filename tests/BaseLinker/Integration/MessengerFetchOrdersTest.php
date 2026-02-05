<?php

declare(strict_types=1);

namespace App\Tests\BaseLinker\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class MessengerFetchOrdersTest extends KernelTestCase
{
    public function testFetchOrdersHandlerIsRegistered(): void
    {
        self::bootKernel();

        $handler = self::getContainer()->get(\App\BaseLinker\MessageHandler\FetchOrdersHandler::class);

        self::assertInstanceOf(\App\BaseLinker\MessageHandler\FetchOrdersHandler::class, $handler);
    }

    public function testMessageBusAndBaseLinkerClientAreAvailable(): void
    {
        self::bootKernel();

        $container = self::getContainer();

        self::assertTrue($container->has(\Symfony\Component\Messenger\MessageBusInterface::class));
        self::assertTrue($container->has(\App\BaseLinker\Client\BaseLinkerClient::class));
        self::assertTrue($container->has(\App\BaseLinker\Service\OrdersService::class));
    }
}
