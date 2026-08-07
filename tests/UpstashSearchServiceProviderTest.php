<?php

declare(strict_types=1);

namespace LaravelUpstashSearch\Scout\Tests;

use Illuminate\Config\Repository;
use Illuminate\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Laravel\Scout\EngineManager;
use LaravelUpstashSearch\Scout\UpstashSearchClient;
use LaravelUpstashSearch\Scout\UpstashSearchEngine;
use LaravelUpstashSearch\Scout\UpstashSearchServiceProvider;
use PHPUnit\Framework\TestCase;

class UpstashSearchServiceProviderTest extends TestCase
{
    private function createApp(): Application
    {
        $app = new Application();
        $app->singleton('config', function () {
            return new Repository([
                'upstash-search' => [
                    'url' => 'https://example.com',
                    'token' => 'test-token',
                ],
                'scout' => [
                    'driver' => 'upstash',
                ],
            ]);
        });
        $app->singleton('events', function ($app) {
            return new Dispatcher($app);
        });

        Application::setInstance($app);

        return $app;
    }

    public function test_registers_upstash_search_client_as_singleton(): void
    {
        $app = $this->createApp();
        $provider = new UpstashSearchServiceProvider($app);
        $provider->register();
        $provider->boot();

        $client = $app->make(UpstashSearchClient::class);

        $this->assertInstanceOf(UpstashSearchClient::class, $client);
    }

    public function test_extends_engine_manager_with_upstash_driver(): void
    {
        $app = $this->createApp();
        $app->singleton(EngineManager::class, function ($app) {
            return new EngineManager($app);
        });

        $provider = new UpstashSearchServiceProvider($app);
        $provider->register();
        $provider->boot();

        $engineManager = $app->make(EngineManager::class);
        $engine = $engineManager->driver('upstash');

        $this->assertInstanceOf(UpstashSearchEngine::class, $engine);
    }
}
