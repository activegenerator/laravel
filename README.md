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
      is: string:200 # results in migration: $table->string('name',200);
    description:
      is: string:255
    price:
      is: decimal:10,2 # results in migration: $table->decimal('price',10,2);
    discount_price:
      is: decimal:10,2
    download_id: # Auto creates relationships on both models with the prop download()
      is: foreignId 
      references: id
      on: files

File:
  fields:
    path:
      is: string
      label: Path
```

## Structure of the yaml file (overview)

```yml
config: # Object - Config on root
mixins: # Array -  Mixin specification
  ...
ModelName: # Object - The singular model name
   config: # Object - Config on model
   fields: # Object - Specify the fields on the model with slugs as key
   relations: # Array - Specify relations
   mixins: # Array - Array of mixins used by the model
   code: # Object - Insert custom code
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
    # - ModelGenerator
    # - JetstreamGenerator
    # - FactoryGenerator
    # - NovaGenerator
  exclude:  # Set this use all generators except the specified
    # - ModelGenerator
    # - ...

ModelName:
    config:
        # All of the above plus:
        titleField: title # The field that should act as a title field when refered to in relation screens
        deaultRelatedTitleField: name # The title field of relations. Only if no relatedTitleField is specified on the relation or no config.titleField is specified on the related model. Nothing specified? 'id' is used.

```

The config object can be set on the root (affecting all models in the file) or on a individual model


## Fields

```yml
City:
    fields:
        name:
            is: string:200 # Type definition of the field (REQUIRED)
            label: Name # Label of the field. default: Str::studly(slug)
            fillable: true # Fillable option on the model, default: true
            casts: string # Casts when needed
            appends: false # appends on the model. default: false
            hidden: false # hidden on the model. default: false
            nullable: true # nullable on the migration. default: true
            default: Amsterdam # Adds a default to attributes on the model.
            # Admin UI options. Used in activegenerator/jetstream and activegenerator/nova
            rules: required|bla|bla # Rules for validation
            listable: false # Whether to show in an index/listening view. default: false
            editable: true # Whether to show in an edit view. default: true
            creatable: true # Whether to show in an create view. default: true
            searchable: true # Whether this field can be searched on. default: true
            filterable: false # Whether to show in an filter view. default: false
            sortable: true # Whether this field can be sorted. default: true
```

## Relations

Below a specfication of all the relations with there properties. Most properties are automatically inferred. The reverse relations is automatically created.

```yml
City:
  relations:
    - is: hasOne # (REQUIRED)
      related: Tag # (REQUIRED) The related model name
      foreignKey: product_id
      localKey: id
      prop: tag # The function name used on the model
      relatedTitleField: name # Default is the config.titleField on the other model or else 'id'
    - is: hasMany # (REQUIRED)
      related: Tag # (REQUIRED)
      foreignKey: product_id
      localKey: id
      prop: tags # The function name used on the model
      relatedTitleField: name # Default is the config.titleField on the other model or else 'id'
    - is: morphOne # (REQUIRED)
      related: Image  # (REQUIRED)
      name: imagable # (REQUIRED)
      type: imagable_type
      id: imagable_id
      localKey: id
      prop: image #
      relatedTitleField: name # Default is the config.titleField on the other model or else 'id'
    - is: morphMany # (REQUIRED)
      related: Image  # (REQUIRED)
      name: imagable # (REQUIRED)
      type: imagable_type
      id: imagable_id
      localKey: id
      prop: images #
      relatedTitleField: name # Default is the config.titleField on the other model or else 'id'
    - is: morphTo # (REQUIRED)
      name: imagable (__FUNCTION__)
      type: imagable_type
      id: imagable_id
      ownerKey: id
      prop: imagable #
      relatedTitleField: name # Default is the config.titleField on the other model or else 'id'
    - is: morphToMany # (REQUIRED)
      related: Tag # (REQUIRED)
      name: taggable # (REQUIRED)
      table: taggables
      foreignPivotKey: taggable_id
      relatedPivotKey: tag_id
      parentKey: id
      relatedKey: id
      prop: tags #
      relatedTitleField: name # Default is the config.titleField on the other model or else 'id'
    - is: morphedByMany # (REQUIRED)
      related: Post # (REQUIRED)
      name: taggable # (REQUIRED)
      table: taggables
      foreignPivotKey: tag_id
      relatedPivotKey: taggable_id
      parentKey: id
      relatedKey: id
      prop: posts #
      relatedTitleField: name # Default is the config.titleField on the other model or else 'id'
    - is: belongsToMany # (REQUIRED)
      related: File # (REQUIRED)
      table: file_product
      foreignPivotKey: product_id
      relatedPivotKey: file_id
      parentKey: id
      relatedKey: id 
      relation: files
      prop: files #
      relatedTitleField: name # Default is the config.titleField on the other model or else 'id'
    - is: hasOneThrough  # (REQUIRED)
      related: Tag # (REQUIRED)
      through: Product # (REQUIRED)
      firstKey: store_id 
      secondKey: product_id 
      localKey: id
      secondLocalKey: id 
      table1: products #
      table2: tags
      prop: tag
      relatedTitleField: name # Default is the config.titleField on the other model or else 'id'
    - is: hasManyThrough  # (REQUIRED)
      related: Tag # (REQUIRED)
      through: Product # (REQUIRED)
      firstKey: store_id 
      secondKey: product_id
      localKey: id
      secondLocalKey: id
      table1: products
      table2: tags
      prop: tags
      relatedTitleField: name # Default is the config.titleField on the other model or else 'id'
```

## Code

You can add custom code inside the model if needed. There are a few slots provided in the model.

```yml
City:
  code: # Add custom code to the output. Example:
    model:
      imports: |
        use Laravel\Nova\Fields\Currency;
        use Laravel\Nova\Fields\Markdown;
        use Illuminate\SomeMixin;
      header:
        use SomeMixin;
      body: |
        public function someFunction() {
            return "Test 123";
        }
    migration:
      imports:
      schema_up:
      up:
      down:
    factory:
      imports:
      definition:
    nova:
      imports:
      fields:
      cards:
      filters:
      lenses:
      actions:
      body:
```

## Mixins

Some times you want to add the same fields to different model. Or you want to always include some Trait or other code. 
Mixins come in handy:

```yml
mixins: # The global mixin specification
  slug: # Name of the mixin
    fields:
      slug:
        is: string:200
        label: Slug
    code:
      model:
        imports: |
          use \Cviebrock\EloquentSluggable\Sluggable;
        header: |
          use Sluggable;
        body: | # Inside strings blade syntax is allowed for some flexibility
          public function sluggable(): array
          {
              return ['slug' => ['source' => '{{ $slug ?? 'name' }}']];
          }

ModelName:
  mixins: # On the model we can specify the mixin to be used
    - is: slug
      slug: name # The object properties are passed onto the mixin. In this case $slug is available in the mixin strings.

```

## Includes

You can include other yml files in a file with ``#!include FILE_NAME`` for example:

```yml
config:
  relationTitleField: name

#!include mixins.yml     ## This will include the contents of mixins.yml here

ModelName:
    # etc.
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
