<?php

namespace App\Services\DatoCms;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class DatoCmsClient
{
    private const API_BASE_URL = 'https://graphql.datocms.com';
    private Client $client;

    public function __construct(
        private readonly string $apiToken,
        private readonly ?string $environment = null,
        private readonly bool $preview = false,
        private readonly ?int $cacheDuration = 3600
    ) {
        $this->client = new Client([
            'base_uri' => self::API_BASE_URL,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function query(string $query, array $variables = []): array
    {
        if (empty($this->apiToken)) {
            throw new RuntimeException('DatoCMS API token is not configured.');
        }

        $cacheKey = $this->getCacheKey($query, $variables);

        if ($this->cacheDuration !== null) {
            return Cache::remember($cacheKey, $this->cacheDuration, function () use ($query, $variables) {
                return $this->executeQuery($query, $variables);
            });
        }

        return $this->executeQuery($query, $variables);
    }

    private function executeQuery(string $query, array $variables): array
    {
        $headers = [];

        if ($this->environment) {
            $headers['X-Environment'] = $this->environment;
        }

        if ($this->preview) {
            $headers['X-Include-Drafts'] = 'true';
        }

        $response = $this->client->post('', [
            'headers' => $headers,
            'json' => [
                'query' => $query,
                // 'variables' => $variables,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (isset($data['errors'])) {
            throw new RuntimeException(
                'DatoCMS GraphQL Error: ' . json_encode($data['errors'])
            );
        }

        return $data['data'] ?? [];
    }

    private function getCacheKey(string $query, array $variables): string
    {
        return 'datocms_' . md5($query . json_encode($variables) . $this->environment . $this->preview);
    }
}
