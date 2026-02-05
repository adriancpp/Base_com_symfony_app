<?php

declare(strict_types=1);

namespace App\BaseLinker\Client;

use App\BaseLinker\Exception\BaseLinkerApiException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Low-level client for BaseLinker connector API.
 * Sends POST with method + parameters, returns decoded response.
 */
final class BaseLinkerClient
{
    public function __construct(
        private readonly string $apiUrl,
        private readonly string $token,
        private readonly HttpClientInterface $httpClient
    ) {
    }

    /**
     * Call a BaseLinker API method.
     *
     * @param array<string, mixed> $parameters
     * @return array<string, mixed> decoded response (full body; check 'status' => 'SUCCESS', data in method-specific keys)
     * @throws BaseLinkerApiException when API returns status ERROR or token is empty
     */
    public function request(string $method, array $parameters = []): array
    {
        if ($this->token === '') {
            throw new BaseLinkerApiException('BaseLinker API token is not configured. Set BASE_LINKER_TOKEN in .env.');
        }

        $body = [
            'method' => $method,
            'parameters' => json_encode($parameters, JSON_THROW_ON_ERROR),
        ];

        $response = $this->httpClient->request('POST', $this->apiUrl, [
            'headers' => [
                'X-BLToken' => $this->token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => $body,
        ]);

        $data = $response->toArray();

        if (($data['status'] ?? '') !== 'SUCCESS') {
            $message = $data['error_message'] ?? $data['error_text'] ?? 'Unknown API error';
            $code = $data['error_code'] ?? null;
            throw new BaseLinkerApiException($message, $code);
        }

        return $data;
    }
}
