<?php

namespace LaravelUpstashSearch\Scout;

use Illuminate\Pagination\LengthAwarePaginator;

class UpstashSearchResult extends LengthAwarePaginator
{
    public function __construct(array $result, int $perPage = 10, ?int $currentPage = 1, array $options = [])
    {
        $documents = $result['documents'] ?? [];
        $total = $result['total'] ?? count($documents);

        parent::__construct($documents, $total, $perPage, $currentPage, $options);
    }
}
