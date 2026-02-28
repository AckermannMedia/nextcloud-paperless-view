<?php

declare(strict_types=1);

namespace OCA\PaperlessView\Service;

use OCA\PaperlessView\AppInfo\Application;
use OCP\Http\Client\IClientService;
use OCP\IConfig;

class PaperlessService {
    private IClientService $clientService;
    private IConfig $config;

    public function __construct(IClientService $clientService, IConfig $config) {
        $this->clientService = $clientService;
        $this->config = $config;
    }

    public function getBaseUrl(): string {
        $url = $this->config->getAppValue(Application::APP_ID, 'paperless_url', '');
        return rtrim($url, '/');
    }

    public function getToken(): string {
        return $this->config->getAppValue(Application::APP_ID, 'paperless_token', '');
    }

    public function isConfigured(): bool {
        return $this->getBaseUrl() !== '' && $this->getToken() !== '';
    }

    /**
     * @return array{body: string, headers: array<string, string[]>}
     */
    public function request(string $endpoint, array $params = []): array {
        $client = $this->clientService->newClient();
        $url = $this->getBaseUrl() . '/api/' . ltrim($endpoint, '/');

        $options = [
            'headers' => [
                'Authorization' => 'Token ' . $this->getToken(),
                'Accept' => 'application/json; version=5',
            ],
            'timeout' => 30,
        ];

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $response = $client->get($url, $options);

        return [
            'body' => $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * Fetch binary content (thumbnails, PDFs).
     *
     * @return array{body: string, contentType: string}
     */
    public function requestBinary(string $endpoint): array {
        $client = $this->clientService->newClient();
        $url = $this->getBaseUrl() . '/api/' . ltrim($endpoint, '/');

        $options = [
            'headers' => [
                'Authorization' => 'Token ' . $this->getToken(),
            ],
            'timeout' => 60,
        ];

        $response = $client->get($url, $options);
        $contentType = $response->getHeader('Content-Type') ?: 'application/octet-stream';

        return [
            'body' => $response->getBody(),
            'contentType' => $contentType,
        ];
    }
}
