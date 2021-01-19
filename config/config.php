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
    // ActiveGenerator\Nova\Generators\NovaGenerator::class,
    // ActiveGenerator\Nova\Generators\NovaPermissionsGenerator::class,
    // ActiveGenerator\Laravel\Generators\ApiGenerator::class,
    ActiveGenerator\Laravel\Generators\ModelGenerator::class,
    // ActiveGenerator\Laravel\Generators\MigrationGenerator::class,
    // ActiveGenerator\Laravel\Generators\FactoryGenerator::class,
    // ActiveGenerator\Laravel\Generators\PolicyGenerator::class,
    // ActiveGenerator\Laravel\Generators\SeederGenerator::class,
  ],
  /**
   * These fields classes will provide extra functionality on the segments.
   * They "wrap around" the segment when isMatchWithSegment() returns true
   * There can only be one match per segment
   */
  "fields" => [
    ActiveGenerator\Laravel\Models\Fields\ForeignIdField::class,
    ActiveGenerator\Laravel\Models\Fields\IdentityField::class,
    ActiveGenerator\Laravel\Models\Fields\TimestampsField::class,
    ActiveGenerator\Laravel\Models\Fields\BaseField::class
  ],
  /**
   * These relations classes will provide extra functionality on the segments.
   * They "wrap around" the segment when isMatchWithSegment() returns true
   * There can only be one match per segment
   */
  "relations" => [
    ActiveGenerator\Laravel\Models\Relations\MorphToRelation::class,
    ActiveGenerator\Laravel\Models\Relations\BelongsToRelation::class,
    ActiveGenerator\Laravel\Models\Relations\BaseRelation::class,
  ]
];
