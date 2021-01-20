<?php

namespace ActiveGenerator\Laravel\Models\Yaml;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class YamlField extends YamlBaseClass {

    public $data = [];
    public $slug = '';
    public YamlFieldType $type;
    public $typeProps = [];

    public $fillable = true;
    public $casts = null;
    public $appends = false;
    public $hidden = false;

    public $nullable = true;
    public $default = null;
    public $rules = "";
    public $editable = true;

    public YamlModel $parent;
    public $title;

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
        $this->setEditable();
        $this->setTitle();

        $this->setRules();

        $this->setMigration();
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

    private function setEditable() {
        $this->editable = $this->get('editable', $this->type->defaultEditable());
    }

    private function setTitle() {
        $this->title = $this->get('title', Str::to($this->slug, 'studly'));
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
