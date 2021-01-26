<?php

namespace ActiveGenerator\Laravel\Models\Yaml;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class YamlField extends YamlBaseClass {

    public $data = [];
    public $slug = '';
    public YamlFieldType $type;
    public $typeProps = [];

    public $fillable;
    public $casts;
    public $appends;
    public $hidden;

    public $nullable;
    public $default;
    public $rules;

    public $listable;
    public $editable;
    public $creatable;
    public $searchable;
    public $filterable;
    public $sortable;

    public YamlModel $parent;
    public $label;

    public function __construct($data, $slug, &$parent)
    {
        parent::__construct();
        $this->parent = &$parent;
        $this->slug = $slug;
        $this->data = $data;

        $this->type = new YamlFieldType($this->get('type', null), $this);

        $this->setNullable();
        $this->setDefault();

        $this->setFillable();
        $this->setCasts();
        $this->setAppends();
        $this->setHidden();
        $this->setLabel();
        $this->setRules();
        $this->setMigration();

        $this->setListable();
        $this->setEditable();
        $this->setCreatable();
        $this->setSearchable();
        $this->setFilterable();
        $this->setSortable();
    }

    /**
     * One field can bind to multiple fields (timestamps for example)
     */
    public static function bind($data, $slug, $parent): YamlCollection
    {
        if (Arr::get($data, 'type') === "timestamps") {
            $data['type'] = "timestamp";
            return new YamlCollection([
                new YamlField($data, "created_at", $parent),
                new YamlField($data, "updated_at", $parent)
            ]);
        }
        return new YamlCollection([new YamlField($data, $slug, $parent)]);
    }

    private function setFillable() {
        $this->fillable = $this->get('fillable', $this->type->defaultFillable() ? $this->parent->get('config.autoFillable', true) : false);
    }

    private function setCasts() {
        $this->casts = $this->get('casts', $this->type->defaultCasts());
    }

    private function setAppends() {
        $this->appends = $this->get('appends', false);
    }

    private function setHidden() {
        $this->hidden = $this->get('hidden', false);
    }

    public function getCrudables() {
        return [
            'listable' => $this->listable,
            'editable' => $this->editable,
            'creatable' => $this->creatable,
            'searchable' => $this->searchable,
            'filterable' => $this->filterable,
            'sortable' => $this->sortable,
        ];
    }

    private function setListable() {
        $this->listable = $this->get('listable', false);
    }
    private function setEditable() {
        $this->editable = $this->get('editable', in_array($this->slug, [
            'id', 'created_at', 'deleted_at', 'updated_at'
        ]) ? false: true);
    }
    private function setCreatable() {
        $this->creatable = $this->get('creatable', in_array($this->slug, [
            'id', 'created_at', 'deleted_at', 'updated_at'
        ]) ? false: true);
    }
    private function setSearchable() {
        $excludedBySlug = in_array($this->slug, [
            'id', 'created_at', 'deleted_at', 'updated_at'
        ]);
        $excludedByType = in_array($this->type->php, [
            'bool'
        ]);

        $this->searchable = $this->get('searchable', $excludedBySlug || $excludedByType ? false: true);
    }
    private function setFilterable() {
        $this->filterable = $this->get('filterable', false);
    }

    private function setSortable() {
        $this->sortable = $this->get('sortable', true);
    }

    private function setLabel() {
        $this->label = $this->get('label', Str::to($this->slug, 'studly'));
    }

    private function setRules() {
        $this->rules = $this->get('rules', '');
    }

    private function setMigration() {
        $this->migration = $this->get('migration', $this->type->migration());
    }

    private function setNullable() {
        $this->nullable = $this->get('nullable', $this->type->defaultNullable() ? $this->parent->get('config.autoNullable', true) : false);
    }

    private function setDefault() {
        $this->default = $this->get('default', null);
    }

}
