<?php

namespace ActiveGenerator\Laravel\Generators;

use ActiveGenerator\Laravel\Generators\Base\Generator;


class ModelGenerator extends Generator {
  public function templatesDir() : string {
    return __DIR__ . "/../../templates";
  }

  public function output() : array {
    return [
      "template" => $this->templatesDir() . "/model.blade.php",
      "output" => '{{ $outputDir }}/{{ $relativeAppPath }}/Models/{{ $yaml->getName("studly singular") }}.php',
    ];
  }

  public function extend() {
    // Used for the use statements
    $this->vars['usedTypes'] = $this->yaml->relations
      ->select(fn($x) => "Illuminate\Database\Eloquent\Relations\\" . $x->Type)
      ->unique()
      ->filter(fn($x) => class_exists($x));
  }
}
