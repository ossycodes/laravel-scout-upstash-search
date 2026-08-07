<?php

namespace LaravelUpstashSearch\Scout;

use GuzzleHttp\Client;

class UpstashSearchIndex
{
    public function __construct(
        protected Client $client,
        protected string $indexName,
    ) {}

    public function upsert(array $documents): array
    {
        return $this->client->request("upsert-data/{$this->indexName}", [
            'documents' => $documents,
        ]);
    }

    public function search(array $params): array
    {
        $params['includeData'] = true;
        $params['includeMetadata'] = true;

        return $this->client->request("search/{$this->indexName}", $params);
    }

    public function delete(array $params): array
    {
        return $this->client->request("delete/{$this->indexName}", $params);
    }

    public function reset(): array
    {
        return $this->client->request("reset/{$this->indexName}", []);
    }

    public function fetch(array $params): array
    {
        return $this->client->request("fetch/{$this->indexName}", $params);
    }

    public function range(array $params): array
    {
        return $this->client->request("range/{$this->indexName}", $params);
    }
}
