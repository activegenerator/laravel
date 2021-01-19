<?php

return [
  /**
   * The directory of the .scma files.
   */
  "schemaDir" => __DIR__ . "/../generator/schemas/",
  /**
   * The output directory of the generators.
   */
  "outputDir" => base_path(),
  /**
   * The directory of the blade template files
   */
  "templatesDir" => __DIR__ . "/../generator/templates/",
  /**
   * Provide the generators that should be used.
   */
  "generators" => [
    ActiveGenerator\Laravel\Generators\ModelGenerator::class,
    ActiveGenerator\Laravel\Generators\MigrationGenerator::class,
    ActiveGenerator\Laravel\Generators\FactoryGenerator::class,
    // ActiveGenerator\Laravel\Generators\PolicyGenerator::class,
    ActiveGenerator\Laravel\Generators\SeederGenerator::class,
  ],
];
