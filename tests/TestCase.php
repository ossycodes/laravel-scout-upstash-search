<?php

declare(strict_types=1);

namespace LaravelUpstashSearch\Scout\Tests;

use Illuminate\Support\ServiceProvider;
use LaravelUpstashSearch\Scout\UpstashSearchServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            UpstashSearchServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('upstash-search.url', 'https://example.com');
        $app['config']->set('upstash-search.token', 'test-token');
        $app['config']->set('scout.driver', 'upstash');
    }
}
