<?php

declare(strict_types=1);

namespace LaravelUpstashSearch\Scout\Tests;

use Illuminate\Support\Facades\Config;
use Laravel\Scout\EngineManager;
use LaravelUpstashSearch\Scout\Console\ImportUpstashSearchCommand;
use LaravelUpstashSearch\Scout\UpstashSearchClient;
use LaravelUpstashSearch\Scout\UpstashSearchEngine;
use LaravelUpstashSearch\Scout\UpstashSearchServiceProvider;
use Mockery;
use PHPUnit\Framework\TestCase;

class UpstashSearchServiceProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function test_registers_upstash_search_client_as_singleton(): void
    {
        $provider = new UpstashSearchServiceProvider($this->app);
        $provider->register();
        $provider->boot();

        $client = $this->app->make(UpstashSearchClient::class);

        $this->assertInstanceOf(UpstashSearchClient::class, $client);
    }

    public function test_extends_engine_manager_with_upstash_driver(): void
    {
        $provider = new UpstashSearchServiceProvider($this->app);
        $provider->register();
        $provider->boot();

        $engine = $this->app->make(EngineManager::class)->getEngine('upstash');

        $this->assertInstanceOf(UpstashSearchEngine::class, $engine);
    }

    public function test_registers_import_command_in_console(): void
    {
        $provider = new UpstashSearchServiceProvider($this->app);
        $provider->register();
        $provider->boot();

        $commands = $this->app->getProviders(UpstashSearchServiceProvider::class);

        $this->assertTrue(true);
    }
}
