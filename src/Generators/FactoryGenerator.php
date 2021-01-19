<?php

namespace ActiveGenerator\Laravel\Generators;

use ActiveGenerator\Laravel\Generators\Base\Generator;


class FactoryGenerator extends Generator {
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
        "template" => $this->templatesDir() . "/factory.blade.php",
        "output" => '{{ $outputDir }}/database/factories/{{ $yaml->getName("Entity") }}Factory.php',
      ]
    ];
  }
}
