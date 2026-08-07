<?php

declare(strict_types=1);

namespace LaravelUpstashSearch\Scout\Tests;

use Illuminate\Support\Collection;
use Laravel\Scout\Builder;
use Laravel\Scout\Searchable;
use LaravelUpstashSearch\Scout\UpstashSearchClient;
use LaravelUpstashSearch\Scout\UpstashSearchEngine;
use LaravelUpstashSearch\Scout\UpstashSearchIndex;
use LaravelUpstashSearch\Scout\UpstashSearchResult;
use Mockery;
use PHPUnit\Framework\TestCase;

class UpstashSearchEngineTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    private function createEngine(?string $url = 'https://example.com', ?string $token = 'token'): UpstashSearchEngine
    {
        return new UpstashSearchEngine($url, $token);
    }

    public function test_search_builds_params_and_returns_result(): void
    {
        $builder = new Builder(new class implements Searchable {
            public function searchableAs() { return 'posts'; }
            public function toSearchableArray() { return []; }
            public function getKey() { return 1; }
            public function getKeyName() { return 'id'; }
            public function getScoutKey() { return 1; }
            public function newCollection(array $models = []) { return new Collection($models); }
            public function newFromBuilder($attributes = []) { return $this; }
            public function setScoutMetadata(array $metadata) { return $this; }
            public function getScoutMetadata() { return []; }
        }, 'query', null, null);

        $client = Mockery::mock(UpstashSearchClient::class);
        $index = Mockery::mock(UpstashSearchIndex::class);
        $index->shouldReceive('search')->once()->with([
            'query' => 'query',
            'topK' => 5,
            'includeData' => true,
            'includeMetadata' => true,
        ])->andReturn(['documents' => [], 'total' => 0]);
        $client->shouldReceive('index')->once()->with('posts')->andReturn($index);

        $this->app->bind(UpstashSearchClient::class, fn () => $client);

        $engine = $this->createEngine();
        $result = $engine->search($builder);

        $this->assertInstanceOf(UpstashSearchResult::class, $result);
    }

    public function test_map_ids_plucks_ids_from_documents(): void
    {
        $engine = $this->createEngine();
        $results = ['documents' => [['id' => '1'], ['id' => '2']]];

        $ids = $engine->mapIds($results);

        $this->assertSame(['1', '2'], $ids->all());
    }

    public function test_map_returns_empty_collection_when_documents_are_empty(): void
    {
        $engine = $this->createEngine();
        $builder = new Builder(new class implements Searchable {
            public function searchableAs() { return 'posts'; }
            public function toSearchableArray() { return []; }
            public function getKey() { return 1; }
            public function getKeyName() { return 'id'; }
            public function getScoutKey() { return 1; }
            public function newCollection(array $models = []) { return new Collection($models); }
            public function newFromBuilder($attributes = []) { return $this; }
            public function setScoutMetadata(array $metadata) { return $this; }
            public function getScoutMetadata() { return []; }
        }, 'query', null, null);

        $results = ['documents' => []];

        $mapped = $engine->map($builder, $results, new class {
            public function newCollection(array $models = []) { return new Collection($models); }
            public function newFromBuilder($attributes = []) { return $this; }
            public function setScoutMetadata(array $metadata) { return $this; }
        });

        $this->assertTrue($mapped->isEmpty());
    }

    public function test_get_total_count_returns_total_or_document_count(): void
    {
        $engine = $this->createEngine();

        $this->assertSame(10, $engine->getTotalCount(['total' => 10]));
        $this->assertSame(2, $engine->getTotalCount(['documents' => [1, 2]]));
    }

    public function test_build_filter_builds_single_value_filter(): void
    {
        $engine = $this->createEngine();
        $filter = $engine->buildFilter(['category' => 'news']);

        $this->assertSame("category = 'news'", $filter);
    }

    public function test_build_filter_builds_array_value_filter(): void
    {
        $engine = $this->createEngine();
        $filter = $engine->buildFilter(['category' => ['news', 'sports']]);

        $this->assertSame("category IN ('news','sports')", $filter);
    }

    public function test_build_filter_returns_null_for_empty_filters(): void
    {
        $engine = $this->createEngine();

        $this->assertNull($engine->buildFilter([]));
    }
}
