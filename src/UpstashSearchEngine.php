<?php

namespace LaravelUpstashSearch\Scout;

use Illuminate\Support\Collection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Searchable;

class UpstashSearchEngine extends Engine
{
    public function __construct(
        protected ?string $url = null,
        protected ?string $token = null,
    ) {}

    public function update($models)
    {
        $models->each(function ($model) {
            $this->index($model)->upsert([
                [
                    'id' => $model->getScoutKey(),
                    'content' => $this->getSearchableContent($model),
                    'metadata' => $this->getMetadata($model),
                ],
            ]);
        });
    }

    public function delete($models)
    {
        $models->each(function ($model) {
            $this->index($model)->delete([
                'ids' => [$model->getScoutKey()],
            ]);
        });
    }

    public function search(Builder $builder)
    {
        $limit = $builder->limit ?: 5;

        $params = [
            'query' => $builder->query,
            'topK' => $limit,
            'includeData' => true,
            'includeMetadata' => true,
        ];

        if ($builder->filters) {
            $params['filter'] = $this->buildFilter($builder->filters);
        }

        $results = $this->index($builder->model)->search($params);

        return new UpstashSearchResult($results, $limit);
    }

    public function paginate(Builder $builder, $perPage, $page)
    {
        $offset = ($page - 1) * $perPage;
        $limit = $perPage;

        $params = [
            'query' => $builder->query,
            'topK' => $limit,
            'includeData' => true,
            'includeMetadata' => true,
        ];

        if ($builder->filters) {
            $params['filter'] = $this->buildFilter($builder->filters);
        }

        $results = $this->index($builder->model)->search($params);

        return new UpstashSearchResult($results, $limit, $offset);
    }

    public function mapIds($results)
    {
        return collect($results['documents'])->pluck('id');
    }

    public function map(Builder $builder, $results, $model)
    {
        if ($results['documents'] === []) {
            return collect();
        }

        $ids = $this->mapIds($results);

        return $model->newCollection($results['documents'])->map(function ($hit) use ($model) {
            return $model->newFromBuilder(
                $hit['content'] ?? $hit['metadata'] ?? []
            )->setScoutMetadata([
                'id' => $hit['id'],
                'score' => $hit['score'] ?? null,
            ]);
        });
    }

    public function getTotalCount($results): int
    {
        return $results['total'] ?? count($results['documents'] ?? []);
    }

    public function flush($model)
    {
        $this->index($model)->reset();
    }

    protected function index($model)
    {
        $indexName = $model->searchableAs();

        return app(UpstashSearchClient::class)->index($indexName);
    }

    protected function getSearchableContent($model)
    {
        $content = [];

        foreach ($model->toSearchableArray() as $key => $value) {
            $content[$key] = $value;
        }

        return $content;
    }

    protected function getMetadata($model)
    {
        $metadata = [];

        if (method_exists($model, 'getScoutMetadata')) {
            $metadata = $model->getScoutMetadata();
        }

        return $metadata;
    }

    protected function buildFilter(array $filters): ?string
    {
        $parts = [];

        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $parts[] = "$field IN (" . implode(',', array_map(fn ($v) => "'$v'", $value)) . ")";
            } else {
                $parts[] = "$field = '$value'";
            }
        }

        return $parts ? implode(' AND ', $parts) : null;
    }
}
