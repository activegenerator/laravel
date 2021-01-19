<?php

namespace ActiveGenerator\Laravel\Generators;

use ActiveGenerator\Laravel\Generators\Base\Generator;


class SeederGenerator extends Generator {
  public function templatesDir() : string {
    return __DIR__ . "/../../templates";
  }

  /**
   * Remove earlier created migrations
   *
   * @return array
   */
  public function output() : array {
    return [
      [
        "template" => $this->templatesDir() . "/seeder.blade.php",
        "output" => '{{ $outputDir }}/database/seeders/{{ $yaml->getName("Entity") }}Seeder.php',
      ]
    ];
  }
}
