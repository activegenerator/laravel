<?php

namespace ActiveGenerator\Laravel\Console;

use ActiveGenerator\Laravel\Generators\Base\Generator;
use ActiveGenerator\Laravel\Models\Yaml\YamlSchema;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use \Illuminate\Contracts\Foundation\Application as ApplicationContract;

class GenerateCommand extends Command
{
    protected $signature = 'activegenerator:build {schema : Path to the schema file} {--include=} {--force : Override existing files}';
    protected $description = 'Run generation of schema file';
    protected Filesystem $fs;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $fs, ApplicationContract $app)
    {
        parent::__construct();

        $this->fs = $fs;
        $this->app = $app;
    }

    public function handle()
    {
        $schemaFilename = $this->argument('schema');
        $included = array_filter(explode(",", $this->option('include')));

        $schemaDir = config("activegenerator.schemaDir") ?? __DIR__ . "/../../generator/schemas/";
        $outputDir = config("activegenerator.outputDir") ?? base_path();
        $templatesDir = config("activegenerator.templatesDir") ?? __DIR__ . "/../../generator/templates/";
        $generators = config("activegenerator.generators") ?? [];
        $schemaString = $this->fs->get($schemaDir . $schemaFilename);

        $yamlSchema = new YamlSchema($schemaString, $included);

        $context = [
          'namespace' => $this->laravel->getNamespace(),
          'outputDir' => $outputDir,
          'userTemplatesDir' => $templatesDir
        ];

        $index = 0;

        $generatedItems = [];

        foreach($yamlSchema->models as $yamlModel) {
          $this->newLine();
          $this->line("\033[32m" . $yamlModel->name . "\033[39m:");
          $this->line("----------------------");

          /**
           * @var Generator
           */
          foreach($generators as $generator) {
            $gen = new $generator($yamlModel, $context, $index);

            // Handle include / exclude
            if (!$yamlModel->isMatchWithGenerator($gen)) continue;

            $gen->fs = $this->fs;
            $gen->command = $this;
            $gen->runningInConsole = $this->app->runningInConsole();

            if (!isset($generatedItems[$generator])) $generatedItems[$generator] = [];

            $generatedItems[$generator][] = [
                'generator' => $gen,
                'generated' => $gen->generate()
            ];
          }

          $index++;
        }

        foreach($generators as $generator) {
            if (isset($generatedItems[$generator])) {
                $generator::onAllGenerated($generatedItems[$generator]);
            }
        }

        $this->info(PHP_EOL . 'Done!' . PHP_EOL);
    }
}
