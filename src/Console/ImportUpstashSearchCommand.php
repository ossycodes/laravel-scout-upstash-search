<?php

namespace LaravelUpstashSearch\Scout\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ImportUpstashSearchCommand extends Command
{
    protected $signature = 'scout:upstash-import {model=*} {--chunk=100}';
    protected $description = 'Import the given model into the Upstash Search index';

    public function handle(): void
    {
        $models = $this->getModels();

        foreach ($models as $model) {
            $this->info("Importing [{$model}] into Upstash Search index...");

            $model->newQuery()
                ->orderBy($model->getKeyName())
                ->chunk($this->option('chunk'), function (Collection $models) use ($model) {
                    $models->searchable();
                });

            $this->info('Imported ['.$model.'] into Upstash Search index.');
        }
    }

    protected function getModels(): array
    {
        $models = (array) $this->argument('model');

        if (count($models) > 0) {
            return array_map(function ($model) {
                return ltrim($model, '\\');
            }, $models);
        }

        if ($this->option('chunk') === 100 && $this->hasOption('models')) {
            $this->warn('You are missing the --models option.');
        }

        return [];
    }
}
