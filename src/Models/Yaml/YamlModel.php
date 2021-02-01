<?php

namespace ActiveGenerator\Laravel\Models\Yaml;

use ActiveGenerator\Laravel\Generators\Base\Generator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class YamlModel extends YamlBaseClass
{
    public YamlCollection $fields;
    public YamlCollection $relations;
    public $data = [];

    public $table;
    // ModelName
    public $name;
    public $nameFull;

    public function __construct($yamlData, $yamlModelName, YamlSchema &$parent)
    {
        parent::__construct($parent);
        $this->data = $yamlData;

        $this->setName($yamlModelName);
        $this->setTable();

        $this->fields = new YamlCollection();

        if (isset($this->data['fields'])) {
            foreach ($this->data['fields'] as $slug => $field) {
                $this->fields = $this->fields->merge(YamlField::bind($field, $slug, $this));
            }
        }

        $this->relations = new YamlCollection();

        if (isset($this->data['relations'])) {
            foreach ($this->data['relations'] as $relation) {
                $this->relations = $this->relations->merge(YamlRelation::bind($relation, $this));
            }
        }

        $relatedRelations = $this->fields
            ->select(fn ($x) => $x->type->relatedRelation())
            ->filter(fn ($x) => $x);

        if ($relatedRelations->any()) {
            foreach ($relatedRelations as $rel) {
                if (!$this->relations->any(fn ($x) => $x->args->foreignKey === $rel->args->foreignKey)) {
                    $this->relations = $this->relations->add($rel);
                }
            }
        }
    }

    private function setTable()
    {
        $this->table = $this->get('config.table', $this->getName('table'));
    }

    public function getCode($getter, $indentation = 0, $default = "") {
        $code = $this->get('code.' . $getter, $default);

        return join(PHP_EOL, array_map(fn($line) => str_pad("", $indentation) . $line, explode(PHP_EOL, $code)));
    }

    private function setName($name) {
        $this->nameOriginal = $name;

        if (Str::contains($name, '\\')) {
            $this->name = Str::afterLast($name, '\\');
            $this->nameFull = $name;
        } else {
            $this->name = $name;
            $this->nameFull = 'App\Models\\' . $name;
        }
    }

    public function getName($to = "")
    {
        return Str::to($this->name, $to);
    }

    public function isMatchWithGenerator(Generator $generator)
    {
        $include = $this->get('config.include');
        $exclude = $this->get('config.exclude');
        $shouldOutput = $include ? in_array($generator->class, $include) : true;
        $shouldIgnore = $exclude ? in_array($generator->class, $exclude) : false;

        return $shouldOutput && !$shouldIgnore;
    }

    public function getLabel($form) {
        return parent::get("config.label." . $form, $this->getName($form));
    }

    public function get($query, $default = '')
    {
        if (str_contains($query, "config.")) {
            // Also check parent
            return Arr::get($this->data, $query, $this->parent->get($query, $default));
        }

        return parent::get($query, $default);
    }
}
