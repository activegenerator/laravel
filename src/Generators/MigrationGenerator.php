<?php

namespace ActiveGenerator\Laravel\Generators;

use ActiveGenerator\Laravel\Generators\Base\Generator;

use ActiveGenerator\Laravel\Helpers\Marker;

class MigrationGenerator extends Generator {

  public function templatesDir() : string {
    return __DIR__ . "/../../templates";
  }

  /**
   * Remove earlier created migrations
   *
   * @return array
   */
  public function output() : array {
    $index = str_pad($this->var('index'), 3, "0", STR_PAD_LEFT);
    $dir = $this->var('outputDir') . '/database/migrations/';
    $filename = $dir . $this->var('date') . $index . "_create_" . $this->yaml->table . '_table.php';

    // $this->vars['json'] = $this->vars["table"]->toJson();
    $this->vars['json'] = "";

    return [
      "template" => $this->templatesDir() . "/migration.blade.php",
      "output" => $filename,
    ];
  }

  public function write(string $filename, string $contents)
  {
    $dir = $this->var('outputDir') . '/database/migrations/';

    $migrations = $this->fs->glob($dir . "/*");

    foreach($migrations as $migration) {
        $migrationText = $this->fs->get($migration);

        if (Marker::hasMark($migrationText, $this->vars['marker']->mark)) {
            unlink($migration);
            break;
        }
    }

    $this->put($filename, $contents);
  }
}
