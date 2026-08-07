<?php

declare(strict_types=1);

namespace LaravelUpstashSearch\Scout\Tests;

use LaravelUpstashSearch\Scout\UpstashSearchResult;
use PHPUnit\Framework\TestCase;

class UpstashSearchResultTest extends TestCase
{
    public function test_constructor_extracts_documents_and_total_from_result(): void
    {
        $result = new UpstashSearchResult([
            'documents' => [['id' => '1'], ['id' => '2']],
            'total' => 2,
        ], 10, 1);

        $this->assertCount(2, $result->items());
    }

    public function test_constructor_uses_document_count_when_total_missing(): void
    {
        $result = new UpstashSearchResult([
            'documents' => [['id' => '1'], ['id' => '2'], ['id' => '3']],
        ], 10, 1);

        $this->assertSame(3, $result->total());
    }
}
