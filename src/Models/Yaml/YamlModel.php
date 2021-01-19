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
    public YamlSchema $parent;

    public $table;

    public function __construct($yamlData, $yamlModelName, YamlSchema &$parent)
    {
        parent::__construct();
        $this->parent = &$parent;
        $this->name = $yamlModelName;
        $this->data = $yamlData;

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

        $defaultRelations = $this->fields
            ->select(fn ($x) => $x->type->defaultRelation())
            ->filter(fn ($x) => $x);

        if ($defaultRelations->any()) {
            foreach ($defaultRelations as $rel) {
                if (!$this->relations->any(fn ($x) => $x->field === $rel->field)) {
                    $this->relations = $this->relations->add($rel);
                }
            }
        }
    }

    private function setTable()
    {
        $this->table = $this->get('config.tableName', $this->getName('table'));
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

    public function get($query, $default = '')
    {
        if (str_contains($query, "config.")) {
            // Also check parent
            return Arr::get($this->data, $query, $this->parent->get($query, $default));
        }

        return Arr::get($this->data, $query, $default);
    }
}
