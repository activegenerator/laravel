<?php

namespace ActiveGenerator\Laravel\Models\Yaml;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class YamlRelation extends YamlBaseClass {

    public $prop;

    public $model; // The other model
    public $modelFull; // The other model including namespace
    public $modelOriginal; // // The other model as given
    public $foreignKey;
    public $type;

    public $table; // Pivottable or other table name
    public $foreignPivotKey;
    public $relatedPivotKey;

    public YamlModel $parent; // the own yaml model
    public ?YamlModel $related; // The other model in yaml if existing

    public function __construct($data, $type, &$parent)
    {
        parent::__construct();
        $this->parent = &$parent;
        $this->type = $type;
        $this->Type = ucfirst($type);
        $this->data = $data;

        $this->setModel();
        $this->setProp();
        $this->setForeignKey();

        // BelongsToMany
        $this->setTable();
        $this->setForeignPivotKey();
        $this->setRelatedPivotKey();
    }

    /**
     * One field can bind to multiple relations
     */
    public static function bind($data, $parent): YamlCollection
    {
        return new YamlCollection([new YamlRelation($data, $data['type'], $parent)]);
    }

    public function defaultRelation() : ?YamlRelation {
        if (!$this->related) return null;

        if ($this->type == "belongsTo") {
            $field = $this->get('autocreatedBy', null);

            if ($field && $field->get('reference', 'id') !== "id") {
                // Doesnt have id as reference
                // @todo Do something with it
            }

            return new YamlRelation([
                'model' => $this->parent->getName('Entity'),
                'autocreatedBy' => $this,
            ], 'hasMany', $this->related);
        }

        if ($this->type == "belongsToMany") {
            return new YamlRelation([
                'model' => $this->parent->getName('Entity'),
                'table' => $this->table,
                'autocreatedBy' => $this,
            ], 'belongsToMany', $this->related);
        }

        return null;
    }

    private function setModel() {
        $this->modelOriginal = $model = $this->get('model');

        if (!$model) {
            throw new Exception("Please define a model on '" . $this->type . "'-relation on '" . $this->parent->name . "'");
        }

        if (Str::contains($model, '\\')) {
            $this->model = Str::afterLast($model, '\\');
            $this->modelFull = $model;
        } else {
            $this->model = $model;
            $this->modelFull = 'App\Models\\' . $model;
        }

        $this->related = $this->parent->parent->models->first(fn($x) => $x->name == $this->model);
    }

    private function setProp() {
        $this->prop = $this->get('prop', Str::to($this->model, $this->isSingular() ? "singular snake" : "plural snake"));
    }

    private function setForeignKey() {
        $this->field = $this->get('foreignKey', Str::to($this->model, "singular snake") . "_id");
    }

    private function setTable() {
        $table = $this->related ? $this->related->table : Str::to($this->model, "plural snake");

        if ($this->type == "belongsToMany") {
            $models = new YamlCollection([
                $this->parent->getName('entity'),
                Str::to($this->model, "singular snake")
            ]);
            $models = $models->sort("asc");
            $table = $models->first() . "_" . $models->last();
        }

        $this->table = $this->get('table', $table);
    }

    private function setForeignPivotKey() {
        $fpk = $this->parent->getName('entity') . "_id";
        $this->foreignPivotKey = $this->get('foreignPivotKey', $fpk);
    }

    private function setRelatedPivotKey() {
        $rpk = Str::to($this->model, "singular snake") . "_id";
        $this->relatedPivotKey = $this->get('relatedPivotKey', $rpk);
    }

    public function isSingular() {
        return [
            'belongsTo' => true,
            'hasOne' => true
        ][$this->type] ?? false;
    }

}
