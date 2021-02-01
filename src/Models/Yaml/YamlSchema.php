<?php

namespace ActiveGenerator\Laravel\Models\Yaml;

use ActiveGenerator\Laravel\Helpers\Template;
use ActiveGenerator\Laravel\Libs\TopSort\ArraySort;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class YamlSchema extends YamlBaseClass {

    public $data;
    public YamlCollection $models;

    public function __construct($string, $included = [])
    {
        parent::__construct();

        $string = $this->parseIncludes($string);

        $this->data = Yaml::parse($string);

        $this->parseMixins();

        $this->models = new YamlCollection();

        // Support for the --include parameter
        if (count($included) > 0) {
            foreach($this->data as $name => $model) {
                if (!in_array($name, $included)) {
                    unset($this->data[$name]);
                }
            }
        }

        $this->createModels();
        $this->createAutomaticRelations();
        $this->createAutomaticFields();
        $this->topologicalSort();
    }

    private function parseIncludes($string) {
        if (!$string) return $string;

        preg_match_all('/#!include:?(.*)/', $string, $output_array);

        foreach($output_array[0] as $key => $match) {
            $file_raw = $output_array[1][$key];
            $file = trim($file_raw);

            $schemaDir = config("activegenerator.schemaDir") ?? __DIR__ . "/../../generator/schemas/";

            try {
                $contents = File::get($schemaDir . $file);
            } catch(Exception $ex) {
                throw new Exception("Could not find include " . $schemaDir . $file);
            }

            $string = str_replace($match, $contents, $string);
        }

        return $string;
    }

    private function parseMixins() {
        if (!$this->data) return;

        $availableMixins = $this->data['mixins'] ?? [];

        foreach($this->data as $name => $model) {
            if ($this->isReserved($name)) continue;
            if (!isset($model['mixins'])) continue;

            foreach($model['mixins'] as $mixinRequest) {

                if (is_string($mixinRequest)) {
                    $mixinRequest = ['is' => $mixinRequest];
                }

                if (!$mixinRequest['is']) continue;

                if (!isset($availableMixins[$mixinRequest['is']])) {
                    throw new Exception("Could not find mixin: " . $mixinRequest['is']);
                }

                $parsedMixin = $this->parseMixin($availableMixins[$mixinRequest['is']], $mixinRequest);

                $this->data[$name] = array_replace_recursive($this->data[$name], $parsedMixin);
            }
        }
    }

    private function parseMixin($mixin, $mixinRequest) {
        return $this->mapRecursive(fn($x) => is_string($x) ? Template::compile($x, $mixinRequest) : $x, $mixin);
    }

    private function mapRecursive($callback, $array)
    {
        $func = function ($item) use (&$func, &$callback) {
            return is_array($item) ? array_map($func, $item) : call_user_func($callback, $item);
        };

        return array_map($func, $array);
    }

    private function isReserved($value) {
        return in_array($value, ['config', 'mixins']);
    }

    private function createModels() {
        if ($this->data) {
            // Dumb double processing to make relations work @todo enhance
            $finalModels = new YamlCollection();;
            foreach($this->data as $name => $model) {
                if ($this->isReserved($name)) continue;
                $this->models = $this->models->add(new YamlModel($model, $name, $this));
            }

            foreach($this->data as $name => $model) {
                if ($this->isReserved($name)) continue;
                $finalModels = $finalModels->add(new YamlModel($model, $name, $this));
            }
            $this->models = $finalModels;
        }
    }

    private function createAutomaticRelations() {
        /**
         * Automatic relations
         */
        foreach($this->models as $model) {
            if ($model->get('config.autoRelations', true)) {

                /**
                * @var YamlRelation
                */
                foreach($model->relations as $relation) {

                    // var_dump($relation);
                    // The default relation will only be available when the related model
                    // is also defined in the yaml file
                    $default = $relation->relatedRelation();

                    // Auto-create opposite relation when needed
                    if ($default) {
                        $model2 = $this->models->first(fn($x) => $x->name == $default->parentYaml->name);

                        if (!$model2->relations->any(fn($x) => $x->related === $default->related)) {
                            $model2->relations = $model2->relations->add($default);
                        }
                    }

                    // var_dump("c", $relation->args->table);

                    // Auto-create a pivot table when needed
                    if (!$relation->get('autocreatedBy') &&
                        $relation->args->table &&
                        ($relation->is === "belongsToMany" || $relation->is === "morphToMany") &&
                        !$this->models->any(fn($x) => $x->table === $relation->args->table)) {

                        $modelName = Str::to($relation->args->table, 'studly');

                        $data = ['fields' => [], 'config' => [
                            'table' => $relation->args->table,
                            'include' => ['MigrationGenerator'],
                            'exclude' => []
                        ]];

                        if ($model->get('config.include')) {
                            $data['config']['include'] = array_merge($data['config']['include'], $model->get('config.include'));
                        }
                        if ($model->get('config.exclude')) {
                            $data['config']['exclude'] = array_merge($data['config']['exclude'], $model->get('config.exclude'));
                        }

                        $data['fields'][$relation->args->foreignPivotKey] = [
                            'type' => 'foreignId',
                            // 'references' => 'id',
                            // 'on' => Str::to($relation->args->foreignPivotKey, 'table')
                        ];

                        $data['fields'][$relation->args->relatedPivotKey] = [
                            'type' => 'foreignId',
                            // 'references' => 'id',
                            // 'on' => Str::to($relation->args->foreignPivotKey, 'table')
                        ];

                        if ($relation->is === "morphToMany") {
                            $data['fields'][$relation->args->type] = [
                                'type' => 'string',
                            ];
                        }

                        $data['autoCreatedBy'] = $relation;

                        $this->models = $this->models->add(new YamlModel($data, $modelName, $this));
                    }
                }
            }
        }

    }

    private function createAutomaticFields() {
        /**
         * Automatic fields
         */
        foreach($this->models as $model) {
            if ($model->get('config.softDeletes', false) &&!$model->fields->first(fn($x) => $x->slug === "deleted_at")) {
                $model->fields = $model->fields->prepend(new YamlField(['type' => 'timestamp'], 'deleted_at', $model));
            }

            if ($model->get('config.autoTimestamps', true) &&!$model->fields->first(fn($x) => $x->slug === "updated_at")) {
                $model->fields = $model->fields->prepend(new YamlField(['type' => 'timestamp'], 'updated_at', $model));
            }

            if ($model->get('config.autoTimestamps', true) &&!$model->fields->first(fn($x) => $x->slug === "created_at")) {
                $model->fields = $model->fields->prepend(new YamlField(['type' => 'timestamp', 'nullable' => true], 'created_at', $model));
            }

            if ($model->get('config.autoIds', true) && !$model->fields->first(fn($x) => $x->slug === "id")) {
                $model->fields = $model->fields->prepend(new YamlField(['type' => 'id'], 'id', $model));
            }
        }
    }

    private function topologicalSort() {
        $sorter = new ArraySort();
        $index = [];

        /**
         * @var Table
         */
        foreach($this->models as $model) {
            $deps = [];

            // Find dependencies based on the foreignId
            $foreignIdFields = $model->fields->filter(fn($x) => $x->type->database == 'foreignId');

            foreach($foreignIdFields as $foreignId) {

                if ($foreignId->get('references') && $foreignId->get('on')) {
                    $deps[] = $foreignId->get('on');
                }
            }

            $index[$model->table] = $model;
            $sorter->add($model->table, $deps);
        }

        $sorted = $sorter->sort();

        $this->models = new YamlCollection(array_map(function($table) use($index) {
            return $index[$table];
        }, $sorted));
    }

}
