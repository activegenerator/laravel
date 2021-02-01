<?php

namespace ActiveGenerator\Laravel\Models\Yaml;

use Illuminate\Support\Str;

class YamlRelationArgs extends YamlBaseClass {
    # hasOne # hasMany
    public $related;
    public $foreignKey;
    public $localKey;

    # belongsTo
    public $ownerKey;
    public $relation;

    # belongsToMany
    public $table;
    public $foreignPivotKey;
    public $relatedPivotKey;
    public $parentKey;
    public $relatedKey;

    # hasOneThrough # hasManyThrough
    public $through;
    public $firstKey;
    public $secondKey;
    public $secondLocalKey;

    # morphOne morphMany # morphTo # morphedByMany # morphToMany
    public $name;
    public $type;
    public $id;

    public function __construct(&$parent)
    {
        parent::__construct($parent);

        $this->setArgs();
    }

    public function getParent($key, $default = null) {
        return $this->parent->get($key, $default);
    }

    public function prepare($item) {
        return $item ? "'" . $item . "'" : 'null';
    }

    public function setArgs() {
        $this->related = $this->parent->relatedOriginal . '::class';

        $this->foreignKey = $this->getParent('foreignKey', $this->guessForeignKey());
        $this->localKey = $this->getParent('localKey');

        $this->ownerKey = $this->getParent('ownerKey');
        $this->relation = $this->getParent('relation');

        $this->name = $this->getParent('name');
        $this->type = $this->getParent('type', $this->guessType());
        $this->id = $this->getParent('id');

        $this->table = $this->getParent('table', $this->guessTable());
        // var_dump("a", $this->table);
        $this->foreignPivotKey = $this->getParent('foreignPivotKey', $this->guessForeignPivotKey());
        $this->relatedPivotKey = $this->getParent('relatedPivotKey', $this->guessRelatedPivotKey());
        $this->parentKey = $this->getParent('parentKey');
        $this->relatedKey = $this->getParent('relatedKey');

        $this->through = $this->getParent('through');
        $this->firstKey = $this->getParent('firstKey');
        $this->secondKey = $this->getParent('secondKey');
        $this->secondLocalKey = $this->getParent('secondLocalKey');


    }

    public function list() {
        if ($this->parent->is === "hasOne" || $this->parent->is === "hasMany") {
            return [
                $this->related,
                $this->prepare($this->foreignKey),
                $this->prepare($this->localKey),
            ];
        }
        if ($this->parent->is === "belongsTo") {
            return [
                $this->related,
                $this->prepare($this->foreignKey),
                $this->prepare($this->ownerKey),
                $this->prepare($this->relation),
            ];
        }
        if ($this->parent->is === "belongsToMany") {
            return [
                $this->related,
                $this->prepare($this->table),
                $this->prepare($this->foreignPivotKey),
                $this->prepare($this->relatedPivotKey),
                $this->prepare($this->parentKey),
                $this->prepare($this->relatedKey),
                $this->prepare($this->relation),
            ];
        }
        if ($this->parent->is === "morphOne" || $this->parent->is === "morphMany") {
            return [
                $this->related,
                $this->prepare($this->name),
                $this->prepare($this->type),
                $this->prepare($this->id),
                $this->prepare($this->localKey),
            ];
        }
        if ($this->parent->is === "morphedByMany" || $this->parent->is === "morphToMany") {
            return [
                $this->related,
                $this->prepare($this->name),
                $this->prepare($this->table),
                $this->prepare($this->foreignPivotKey),
                $this->prepare($this->relatedPivotKey),
                $this->prepare($this->parentKey),
                $this->prepare($this->relatedKey),
            ];
        }
        if ($this->parent->is === "morphTo") {
            return [
                $this->prepare($this->name),
                $this->prepare($this->type),
                $this->prepare($this->id),
                $this->prepare($this->ownerKey),
            ];
        }
        if ($this->parent->is === "hasOneThrough" || $this->parent->is === "hasManyThrough" ) {
            return [
                $this->related,
                $this->prepare($this->through),
                $this->prepare($this->firstKey),
                $this->prepare($this->secondKey),
                $this->prepare($this->localKey),
                $this->prepare($this->secondLocalKey),
            ];
        }

        return [];
    }

    public function display() {
        $list = $this->list();

        $lastKey = 0;

        foreach($list as $key => $item) {
            if ($item !== "null") $lastKey = $key;
        }
        return join(", ", array_splice($list, 0, $lastKey + 1));
    }

    public function guessTable() {
        if ($this->parent->is == "belongsToMany") {

            $models = new YamlCollection([
                $this->parent->parent->getName('singular snake'),
                Str::to($this->parent->related, "singular snake")
            ]);

            $models = $models->sort("asc");

            // var_dump("g", $models->first() . "_" . $models->last());
            return $models->first() . "_" . $models->last();
        }
        if ($this->parent->is == "morphToMany") {
            return Str::to($this->name, "plural");
        }
        return null;
    }

    public function guessForeignKey() {
        if ($this->parent->is == "belongsTo") {
            return $this->parent->str('related', 'snake') . "_id";
        }
        return null;
    }

    public function guessForeignPivotKey() {
        if ($this->parent->is == "belongsToMany") {
            return $this->parent->parent->getName("snake") . "_id";
        }
        if ($this->parent->is == "morphToMany") {
            return $this->name . "_id";
        }
        return null;
    }

    public function guessRelatedPivotKey() {
        if ($this->parent->is == "belongsToMany") {
            return Str::to($this->parent->related, "snake") . "_id";
        }
        if ($this->parent->is == "morphToMany") {
            return Str::to($this->parent->related, "snake") . "_id";
        }
        return null;
    }

    public function guessType() {
        if ($this->parent->is == "morphToMany") {
            return $this->name . "_type";
        }
        return null;
    }
}
