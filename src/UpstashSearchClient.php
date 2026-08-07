<?php

namespace LaravelUpstashSearch\Scout;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class UpstashSearchClient
{
    protected Client $client;

    public function __construct(
        protected string $url,
        protected string $token,
    ) {
        $this->client = new Client([
            'base_uri' => rtrim($url, '/') . '/',
            'headers' => [
                'Authorization' => "Bearer {$token}",
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function index(string $indexName): UpstashSearchIndex
    {
        return new UpstashSearchIndex($this->client, $indexName);
    }

    public function request(string $path, array $body = []): array
    {
        try {
            $response = $this->client->post($path, [
                'json' => $body,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException("Upstash Search request failed: " . $e->getMessage(), 0, $e);
        }
    }
}
