<?php

declare(strict_types=1);

namespace LaravelUpstashSearch\Scout\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use LaravelUpstashSearch\Scout\UpstashSearchClient;
use LaravelUpstashSearch\Scout\UpstashSearchIndex;
use PHPUnit\Framework\TestCase;

class UpstashSearchClientTest extends TestCase
{
    public function test_constructs_with_url_and_token(): void
    {
        $client = new UpstashSearchClient('https://example.com', 'token');

        $this->assertInstanceOf(UpstashSearchClient::class, $client);
    }

    public function test_index_returns_upstash_search_index(): void
    {
        $client = new UpstashSearchClient('https://example.com', 'token');
        $index = $client->index('posts');

        $this->assertInstanceOf(UpstashSearchIndex::class, $index);
    }

    public function test_request_returns_decoded_json_response(): void
    {
        $responseBody = json_encode(['ok' => true, 'data' => ['id' => '1']]);

        $guzzle = $this->createMock(Client::class);
        $guzzle->method('post')
            ->willReturn(new \GuzzleHttp\Psr7\Response(200, [], $responseBody));

        $client = new UpstashSearchClient('https://example.com', 'token');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($client, $guzzle);

        $result = $client->request('search/posts', ['query' => 'laravel']);

        $this->assertSame(['ok' => true, 'data' => ['id' => '1']], $result);
    }

    public function test_request_throws_runtime_exception_on_guzzle_exception(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Upstash Search request failed:');

        $guzzle = $this->createMock(Client::class);
        $guzzle->method('post')
            ->willThrowException(new \GuzzleHttp\Exception\RequestException('Error', new \GuzzleHttp\Psr7\Request('POST', 'test')));

        $client = new UpstashSearchClient('https://example.com', 'token');
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($client, $guzzle);

        $client->request('search/posts', ['query' => 'laravel']);
    }
}
