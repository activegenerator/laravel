<?php

namespace ActiveGenerator\Laravel\Models\Yaml;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class YamlRelation extends YamlBaseClass {
    public $is;
    public $prop;
    public $label;
    public $rules;
    public YamlRelationArgs $args;

    public $related; // The other model
    public $relatedFull; // The other model including namespace
    public $relatedOriginal; // // The other model as given
    protected ?YamlModel $relatedYaml; // The other model in yaml if existing
    public $relatedTitleField;

    public $listable;
    public $editable;
    public $creatable;
    public $searchable;
    public $filterable;
    public $sortable;

    // public $foreignKey;

    // public $table; // Pivottable or other table name
    // public $foreignPivotKey;
    // public $relatedPivotKey;

    public function __construct($data, &$parent)
    {
        parent::__construct($parent);

        if ( empty($data['is']) )  {
            throw new Exception("Please specify an 'is' property on this relation in '" . $this->parent->name . ".relations'");
        }

        $this->is = $data['is'];
        $this->Is = ucfirst($this->is);
        $this->data = $data;

        if ( ($this->is === "hasOneThrough" || $this->is === "hasManyThrough") &&
            !$this->get('through')) {
            throw new Exception("Please define a 'through' property on the " . $this->is . "-relation in '" . $this->parent->name . ".relations");
        }

        if ( ($this->is === "morphOne" || $this->is === "morphMany" || $this->is === "morphedByMany" || $this->is == "morphToMany") &&
            !$this->get('name')) {
            throw new Exception("Please define a 'name' property on the " . $this->is . "-relation in '" . $this->parent->name . ".relations");
        }

        $this->setRelated();
        $this->setProp();
        // $this->setForeignKey();

        $this->setRelatedTitleField();
        $this->setLabel();
        $this->setRules();

        $this->args = new YamlRelationArgs($this);

        $this->setListable();
        $this->setEditable();
        $this->setCreatable();
        $this->setSearchable();
        $this->setFilterable();
        $this->setSortable();

        // BelongsToMany
        // $this->setTable();
        // $this->setForeignPivotKey();
        // $this->setRelatedPivotKey();
    }

    /**
     * One field can bind to multiple relations
     */
    public static function bind($data, $parent): YamlCollection
    {
        return new YamlCollection([new YamlRelation($data, $parent)]);
    }

    public function relatedFields() : array {

    }

    public function relatedRelation() : ?YamlRelation {
        if (!$this->relatedYaml) return null;
        // $defaultProp = $this->parent->getName('camel ' . ($this->isSingular() ? 'singular' : 'plural'));

        if ($this->is == "belongsTo") {
            $field = $this->get('autocreatedBy', null);

            if ($field && $field->get('reference', 'id') !== "id") {
                // Doesnt have id as reference
                // @todo Do something with it
            }

            return new YamlRelation(array_merge([
                'is' => 'hasMany',
                'related' => $this->parent->getName('Entity'),
                'rules' => $this->rules,
                'foreignKey' => $this->args->foreignKey,
                'localKey' => $this->args->ownerKey,
                'autocreatedBy' => $this,
                'prop' => $this->parent->str('name', 'camel plural'),
            ], $this->getCrudables()), $this->relatedYaml);
        }

        if ($this->is == "hasMany" || $this->is == "hasOne") {
            return new YamlRelation(array_merge([
                'is' => 'belongsTo',
                'related' => $this->parent->getName('Entity'),
                'rules' => $this->rules,
                'foreignKey' => $this->args->foreignKey,
                'ownerKey' => $this->args->localKey,
                'prop' => $this->parent->str('name', 'camel singular'),
            ], $this->getCrudables()), $this->relatedYaml);
        }

        if ($this->is == "belongsToMany") {
            return new YamlRelation(array_merge([
                'is' => 'belongsToMany',
                'related' => $this->parent->getName('Entity'),
                'rules' => $this->rules,
                'table' => $this->args->table,
                'foreignPivotKey' => $this->args->relatedPivotKey,
                'relatedPivotKey' => $this->args->foreignPivotKey,
                'parentKey' => $this->args->relatedKey,
                'relatedKey' => $this->args->parentKey,
                'autocreatedBy' => $this,
                'prop' => $this->parent->str('name', 'camel plural'),
            ], $this->getCrudables()), $this->relatedYaml);
        }

        if ($this->is == "morphToMany") {
            return new YamlRelation(array_merge([
                'is' => 'morphedByMany',
                'related' => $this->parent->getName('Entity'),
                'rules' => $this->rules,
                'name' => $this->args->name,
                'table' => $this->args->table,
                'foreignPivotKey' => $this->args->relatedPivotKey,
                'relatedPivotKey' => $this->args->foreignPivotKey,
                'parentKey' => $this->args->relatedKey,
                'relatedKey' => $this->args->parentKey,
                'autocreatedBy' => $this,
                'prop' => $this->parent->str('name', 'camel plural'),
            ], $this->getCrudables()), $this->relatedYaml);
        }

        if ($this->is == "morphedByMany") {
            return new YamlRelation(array_merge([
                'is' => 'morphToMany',
                'related' => $this->parent->getName('Entity'),
                'rules' => $this->rules,
                'name' => $this->args->name,
                'table' => $this->args->table,
                'foreignPivotKey' => $this->args->relatedPivotKey,
                'relatedPivotKey' => $this->args->foreignPivotKey,
                'parentKey' => $this->args->relatedKey,
                'relatedKey' => $this->args->parentKey,
                'autocreatedBy' => $this,
                'prop' => $this->parent->str('name', 'camel plural'),
            ], $this->getCrudables()), $this->relatedYaml);
        }

        if ($this->is == "morphOne" || $this->is == "morphMany") {
            return new YamlRelation(array_merge([
                'is' => 'morphTo',
                'name' => $this->args->name,
                'type' => $this->args->type,
                'id' => $this->args->id,
                'ownerKey' => $this->args->localKey,
                'prop' => $this->args->name,
                'prop' => $this->parent->str('name', 'camel ' . ($this->is == "morphOne" ? 'singular' : 'plural')),
            ], $this->getCrudables()), $this->relatedYaml);
        }

        return null;
    }

    private function setRelated() {
        $this->relatedOriginal = $related = $this->get('related');

        if (!$related && $this->is !== "morphTo") {
            throw new Exception("Please define a 'related' property on the " . $this->is . "-relation in '" . $this->parent->name . ".relations");
        }

        if (Str::contains($related, '\\')) {
            $this->related = Str::afterLast($related, '\\');
            $this->relatedFull = $related;
        } else {
            $this->related = $related;
            $this->relatedFull = '\App\Models\\' . $related;
        }

        $this->relatedYaml = $this->parent->parent->models->first(fn($x) => $x->name == $this->related);
    }

    private function setProp() {
        $default = Str::to($this->related, $this->isSingular() ? "singular snake" : "plural snake");
        if ($this->is === "morphTo") {
            $default = Str::to($this->related, "singular snake") . "able";
        }
        $this->prop = $this->get('prop', $default);
    }

    private function setLabel() {
        $this->label = $this->get('label', Str::to($this->prop, $this->isSingular() ? "singular studly" : "plural studly"));
    }

    private function setRules() {
        $this->rules = $this->get('rules', '');
    }

    private function setRelatedTitleField() {
        if ($this->get('relatedTitleField')) {
            $this->relatedTitleField = $this->get('relatedTitleField');
            return;
        }
        if ($this->relatedYaml)
            $this->relatedTitleField = $this->relatedYaml->get('config.titleField', 'id');
        else
            $this->relatedTitleField = $this->parent->parent->get('config.defaultRelatedTitleField', 'id');
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
        $this->editable = $this->get('editable', true);
    }
    private function setCreatable() {
        $this->creatable = $this->get('creatable', true);
    }
    private function setSearchable() {
        $this->searchable = $this->get('searchable', false);
    }
    private function setFilterable() {
        $this->filterable = $this->get('filterable', false);
    }
    private function setSortable() {
        $this->sortable = $this->get('sortable', false);
    }

    public function isSingular() {
        return in_array($this->is, [
            'belongsTo',
            'hasOne',
            'hasOneThrough',
            'morphOne'
        ]);
    }

}
