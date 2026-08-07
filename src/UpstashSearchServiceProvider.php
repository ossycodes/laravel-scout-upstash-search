<?php

namespace LaravelUpstashSearch\Scout;

use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use LaravelUpstashSearch\Scout\Console\ImportUpstashSearchCommand;

class UpstashSearchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->singleton(UpstashSearchClient::class, function ($app) {
            return new UpstashSearchClient(
                $app['config']['upstash-search.url'],
                $app['config']['upstash-search.token'],
            );
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportUpstashSearchCommand::class,
            ]);
        }

        resolve(EngineManager::class)->extend('upstash', function ($app) {
            return new UpstashSearchEngine(
                $app['config']['upstash-search.url'],
                $app['config']['upstash-search.token'],
            );
        });
    }
}
