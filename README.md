# ActiveGenerator
The code generator for Laravel. Build your Laravel project from a single yml schema file

## Installation
In your laravel directory run:

```
composer require activegenerator/laravel --dev
```

## Configuration

```
php artisan activegenerator:publish config
```
The config file is now accessabile in ```config/activegenerator.php```. Besides configuring the right input (```schemaDir```) and output (```outputDir```) directories, you can hook the generators you want to use here. See the specific generator package for the availble Generators.

```php
...
  /**
   * Provide the generators that should be used.
   */
  "generators" => [
    ActiveGenerator\Laravel\MigrationGenerator::class,
    ActiveGenerator\Laravel\ModelGenerator::class,
    ...
  ],
...
```

## Writing the yml

Go ahead and publish the ```example.yml``` file to the ```generator/schemas``` directory:
```
php artisan activegenerator:publish schemas
```
Below a minimal example yml file:

```yml
Product:
  fields:
    name:
      type: string:200 # results in migration: $table->string('name', 200);
    description:
      type: string:255
    price:
      type: decimal:10,2
    discount_price:
      type: decimal:10,2
    download_id: # Auto creates relationships on both models
      type: foreignId 
      references: id
      on: files

File:
  fields:
    path:
      type: string
      title: Path
```

## Config object & defaults

The defaults of the config object:

```yml
config:
  softDeletes: false # softDeletes will auto create deleted_at and turn on softDeletes in the model
  autoIds: true # Will auto create ids
  autoTimestamps: true # Will auto create timestamps like 'created_at' and 'updated_at'
  autoRelations: true # Will auto create relations based on 'foreignId', or creating the opposite relation. Also auto creates missing pivot tables
  autoNullable: true # Will auto set all fields to nullable except for 'id', overridable on field level
  autoFillable: true # Will auto set all fields als fillable except for 'id', overridable on field level
  include:  # Set this to only use specific Generators
    # - MigrationGenerator
  exclude:  # Set this use all generators except the specified
    # - ModelGenerator
```

The config object can be set on the root (effecting all models in the file) or on a individual model

## All options on the yaml file

All properties are optional except for props with ```# Mandatory```. The other props will be inferred by naming-convention unless explicitly specified.

```yml
Product:
  config:
    ...
  code: # Add custom code to some files. Example:
    model:
      imports: |
        use Laravel\Nova\Fields\Currency;
        use Laravel\Nova\Fields\Markdown;
      header:
      body: |
        public function someFunction() {
            return "Test 123";
        }
  fields:
    name:
      type: string:200 # Mandatory  - Results in migration: $table->string('name', 200);
      title: Name
      rules: required|bla|bla
      casts: string
      fillable: true
      appends: false
      hidden: false
      nullable: true
    category_id:
      type: foreignId # Mandatory  - Results in migration: $table->foreignId('category_id')->references('id')->on('categories');
      references: id
      on: categories
  relations:
    - type: belongsTo # Mandatory  - Other types: hasMany hasOne
      model: File # Mandatory     
      table: files
      prop: files
      foreignKey: download_id
    - type: belongsToMany # Mandatory
      model: File # Mandatory     
      table: 'file_products'
      foreignPivotKey: 'product_id'
      relatedPivotKey: 'file_id'
      prop: files

Category:
  fields:
    name:
      type: string # Mandatory

```


## Changing the templates

```
php artisan activegenerator:publish templates
```

The templates will be available in the generator/templates directory


## Generate!

```
php artisan activegenerator:build example.yml
```

Use ```--force``` to overwrite existing files
Use ```--include=List,OfModels``` to only output some models
