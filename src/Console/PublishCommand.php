<?php

namespace ActiveGenerator\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
class PublishCommand extends Command
{
    protected $signature = 'activegenerator:publish {tag : config, schemas, templates or all } {--force : Override existing files}';
    protected $description = 'Publish the generator files';

    public function handle()
    {
        $tag = $this->argument('tag');
        $force = $this->option('force');

        Artisan::call('vendor:publish', [
          '--tag' => $tag,
          '--force' => $force,
          '--provider' => 'ActiveGenerator\Laravel\GeneratorServiceProvider'
        ], $this->getOutput());
    }
}
