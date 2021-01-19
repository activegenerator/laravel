<?php

namespace ActiveGenerator\Laravel;

use ActiveGenerator\Laravel\Console\GenerateCommand;
use ActiveGenerator\Laravel\Console\PublishCommand;
use ActiveGenerator\Laravel\Generators\Base\Generator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use ActiveGenerator\Laravel\Models\Yaml\YamlModel;
use ActiveGenerator\Laravel\Models\Yaml\YamlSchema;
use Illuminate\Support\Str;

class GeneratorServiceProvider extends ServiceProvider
{
  public function register()
  {
    $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'activegenerator');

    $this->commands([
        GenerateCommand::class,
        PublishCommand::class,
    ]);
  }

  public function boot(Filesystem $filesystem)
  {
    Str::macro('to', function ($str, $list) {
        $map = [
            "entities" => "plural snake",
            "Entities" => "plural studly",
            "entity" => "singular snake",
            "Entity" => "singular studly",
            "table" => "plural snake",
        ];

        if (isset($map[$list])) {
            $str = str_replace("_id", "", $str);
            $list = $map[$list];
        }

        $list = explode(" ", $list);

        foreach($list as $method) {
            $str = Str::$method($str);
        }

        return $str;
    });


    if ($this->app->runningInConsole()) {
      $pubConfig = [
        __DIR__.'/../config/config.php' => config_path('activegenerator.php'),
      ];

      $this->publishes($pubConfig, 'config');

      $pubSchemas = [
        __DIR__.'/../generator/schemas/example.yml' => base_path() . "/generator/schemas/example.yml",
      ];

      $this->publishes($pubSchemas, 'schemas');

      $generators = config("activegenerator.generators") ?? [];
      $mainTemplatesDir = config("activegenerator.templateDir") ?? base_path() . "/generator/templates/";

      $pubGenerators = [];

      foreach($generators as $generator) {
        /**
        * @var Generator
        */
        if (!class_exists($generator)) continue;

        $schema = new YamlSchema("");
        $generator = new $generator(new YamlModel([], "", $schema));
        $output = $generator->output();

        $output = isset($output['template']) ? [$output] : $output;

        foreach($output as $definition) {
          $relativeTemplate = $generator->getRelativeTemplate($definition);
          $pubGenerators[$definition['template']] = $mainTemplatesDir . "/" . $generator->class . "/" . $relativeTemplate;
        }
      }

      $this->publishes($pubGenerators, 'templates');

      $this->publishes(array_merge($pubConfig, $pubSchemas, $pubGenerators), 'all');
    }
  }

}
