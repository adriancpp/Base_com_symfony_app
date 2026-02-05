<?php

declare(strict_types=1);

namespace App\Tests\BaseLinker\Client;

use App\BaseLinker\Client\BaseLinkerClient;
use App\BaseLinker\Exception\BaseLinkerApiException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class BaseLinkerClientTest extends TestCase
{
    private const API_URL = 'https://api.baselinker.com/connector.php';

    public function testRequestThrowsWhenTokenIsEmpty(): void
    {
        $client = new BaseLinkerClient('', '', new MockHttpClient());

        $this->expectException(BaseLinkerApiException::class);
        $this->expectExceptionMessage('BASE_LINKER_TOKEN');

        $client->request('getOrders', []);
    }

    public function testRequestReturnsDecodedResponseOnSuccess(): void
    {
        $response = new MockResponse(json_encode([
            'status' => 'SUCCESS',
            'orders' => [['order_id' => 1, 'order_source' => 'allegro']],
        ], JSON_THROW_ON_ERROR));

        $httpClient = new MockHttpClient($response);
        $client = new BaseLinkerClient(self::API_URL, 'test-token', $httpClient);

        $result = $client->request('getOrders', ['date_from' => 12345]);

        self::assertSame('SUCCESS', $result['status']);
        self::assertIsArray($result['orders']);
        self::assertCount(1, $result['orders']);
        self::assertSame(1, $result['orders'][0]['order_id']);
    }

    public function testRequestThrowsOnApiError(): void
    {
        $response = new MockResponse(json_encode([
            'status' => 'ERROR',
            'error_message' => 'Invalid token',
            'error_code' => 'ERR_TOKEN',
        ], JSON_THROW_ON_ERROR));

        $httpClient = new MockHttpClient($response);
        $client = new BaseLinkerClient(self::API_URL, 'bad-token', $httpClient);

        $this->expectException(BaseLinkerApiException::class);
        $this->expectExceptionMessage('Invalid token');

        $client->request('getOrderStatusList', []);
    }

    public function testRequestSendsPostToApiUrl(): void
    {
        $response = new MockResponse(json_encode(['status' => 'SUCCESS'], JSON_THROW_ON_ERROR));
        $httpClient = new MockHttpClient($response);
        $client = new BaseLinkerClient(self::API_URL, 'my-token', $httpClient);

        $client->request('getOrders', ['date_from' => 999]);

        self::assertSame('POST', $response->getRequestMethod());
        self::assertSame(self::API_URL, $response->getRequestUrl());
    }
}
