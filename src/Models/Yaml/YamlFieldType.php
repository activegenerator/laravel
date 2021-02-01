<?php

namespace ActiveGenerator\Laravel\Models\Yaml;

use ActiveGenerator\Laravel\BaseClass;
use ActiveGenerator\Laravel\Helpers\FakerHelper;
use Exception;
use Illuminate\Support\Str;

class YamlFieldType extends YamlBaseClass {
    public ?string $database = null;
    public ?string $php = null;
    public ?string $swagger = null;
    public ?string $example = null;
    public ?string $fakerMethod = null;

    public array $props = [];
    public string $propsOriginal = "";

    public function __construct($type, &$parent)
    {
        parent::__construct($parent);
        $segs = explode(":", $type);
        $this->database =  $segs[0] ?? null;
        $this->props = explode(",", $segs[1] ?? "");
        $this->propsOriginal = $segs[1] ?? "";

        $this->setTypeInfo();
    }

    public function defaultFillable() {
        return [
            'id' => false
        ][$this->database] ?? true;
    }

    public function defaultCasts() {
        return [
            'timestamp' => 'datetime',
            'timestampTz' => 'datetime',
            'datetime' => 'datetime',
            'datetimeTz' => 'datetime'
        ][$this->database] ?? null;
    }

    public function defaultNullable() {
        return [
            'id' => false
        ][$this->database] ?? true;
    }

    public function relatedRelation() {
        if ($this->database == "foreignId") {
            $slug = $this->parent->slug;
            $slugNoId = str_replace("_id", "", $slug);
            $on = $this->parent->get('on', $slugNoId);
            $onRelated = Str::to($on, "studly singular");

            return new YamlRelation([
                'is' => 'belongsTo',
                'related' => $onRelated,
                'foreignKey' => $slug,
                'autocreatedBy' => $this->parent,
                'rules' => $this->parent->rules,
                'prop' => $slugNoId,
            ], $this->parent->parent);
        }

        return null;
    }

    public function migration() {
        $base = "\$table->" . $this->database . "('" . $this->parent->slug . "'" . ($this->propsOriginal ? "," . $this->propsOriginal : "") . ")";

        $addition = "";

        if ($this->parent->nullable) {
            $addition .= "->nullable()";
        }

        if ($this->parent->get('references')) {
            $addition .= "->references('" . $this->parent->get('references') . "')";
        }

        if ($this->parent->get('on')) {
            $addition .= "->on('" . $this->parent->get('on') . "')";
        }

        // var_dump($base . $addition . ";");

        return $base . $addition . ";";
    }

    /**
     * Get some useful information about the type
     */
    public function setTypeInfo()
    {
        $faker = \Faker\Factory::create();

        $exampleString = $this->wrap_quotes($faker->word);
        $exampleInteger = '' . $faker->randomDigit;
        $exampleNumber = '' . $faker->randomNumber(1);
        $databaseType = $this->database;

        $bestProp = FakerHelper::getBestFakerPropertyForSlug($this->parent->slug);

        if ($bestProp) {
            try {
                $exampleString = $this->wrap_quotes($faker->{$bestProp});
            } catch(Exception $ex) {
                $bestProp = null;
                $exampleString = $this->wrap_quotes($faker->word);
            }
        }

        // if ($databaseType === "enum") {
        //     $args = $this->segment->methods->first()->arguments->nth(1) ?? [];

        //     $exampleString = "Arr::random(" . json_encode($args) . ")";
        // }

        $output = ([
            "id" => ["phpType" => "int", "swaggerType" => "integer", "example" => $exampleInteger, "fakerMethod" => $bestProp ?? "randomDigit"],
            "foreignId" => ["phpType" => "int", "swaggerType" => "integer", "example" => $exampleInteger, "fakerMethod" => $bestProp ?? "randomDigit"],
            "integer" => ["phpType" => "int", "swaggerType" => "integer", "example" => $exampleInteger, "fakerMethod" => $bestProp ?? "randomDigit"],
            "tinyInteger" => ["phpType" => "int", "swaggerType" => "integer", "example" => $exampleInteger, "fakerMethod" => $bestProp ?? "randomDigit"],
            "mediumInteger" => ["phpType" => "int", "swaggerType" => "integer", "example" => $exampleInteger, "fakerMethod" => $bestProp ?? "randomDigit"],
            "smallInteger" => ["phpType" => "int", "swaggerType" => "integer", "example" => $exampleInteger, "fakerMethod" => $bestProp ?? "randomDigit"],
            "bigInteger" => ["phpType" => "int", "swaggerType" => "integer", "example" => $exampleInteger, "fakerMethod" => $bestProp ?? "randomDigit"],
            "unsignedInteger" => ["phpType" => "int", "swaggerType" => "integer", "example" => $exampleInteger, "fakerMethod" => $bestProp ?? "randomDigit"],
            "unsignedTinyInteger" => ["phpType" => "int", "swaggerType" => "integer", "example" => $exampleInteger, "fakerMethod" => $bestProp ?? "randomDigit"],
            "unsignedMediumInteger" => ["phpType" => "int", "swaggerType" => "integer", "example" => $exampleInteger, "fakerMethod" => $bestProp ?? "randomDigit"],
            "unsignedSmallInteger" => ["phpType" => "int", "swaggerType" => "integer", "example" => $exampleInteger, "fakerMethod" => $bestProp ?? "randomDigit"],
            "unsignedBigInteger" => ["phpType" => "float", "swaggerType" => "integer", "example" => $exampleInteger, "fakerMethod" => $bestProp ?? "randomDigit"],
            "float" => ["phpType" => "float", "swaggerType" => "number", "example" => $exampleNumber, "fakerMethod" => $bestProp ?? "randomNumber(2)"],
            "decimal" => ["phpType" => "float", "swaggerType" => "number", "example" => $exampleNumber, "fakerMethod" => $bestProp ?? "randomNumber(2)"],
            "unsignedDecimal" => ["phpType" => "int", "swaggerType" => "number", "example" => $exampleNumber, "fakerMethod" => $bestProp ?? "randomNumber(2)"],
            "year" => ["phpType" => "int", "swaggerType" => "integer", "example" => $faker->year(), "fakerMethod" => $bestProp ?? "year"],
            // "month" => ["phpType" => "int", "swaggerType" => "integer", "example" => $faker->month(), "fakerMethod" => $bestProp ?? "month"],
            "string" => ["phpType" => "string", "swaggerType" => "string", "example" => $exampleString, "fakerMethod" => $bestProp ?? "word"],
            "uuid" => ["phpType" => "string", "swaggerType" => "string", "example" => $this->wrap_quotes($faker->uuid), "fakerMethod" => $bestProp ?? "uuid"],
            "macAddress" => ["phpType" => "string", "swaggerType" => "string", "example" => $this->wrap_quotes($faker->macAddress), "fakerMethod" => $bestProp ?? "macAddress"],
            "varchar" => ["phpType" => "string", "swaggerType" => "string", "example" => $exampleString, "fakerMethod" => $bestProp ?? "word"],
            "lineString" => ["phpType" => "string", "swaggerType" => "string", "example" => $exampleString, "fakerMethod" => $bestProp ?? "word"],
            "ipAddress" => ["phpType" => "string", "swaggerType" => "string", "example" => $this->wrap_quotes($faker->ipv4()), "fakerMethod" => $bestProp ?? "ipv4"],
            "rememberToken" => ["phpType" => "string", "swaggerType" => "string", "example" => $exampleString, "fakerMethod" => $bestProp ?? "word"],

            "text" => ["phpType" => "string", "swaggerType" => "string", "example" => $this->wrap_quotes($faker->text()), "fakerMethod" => $bestProp ?? "paragraph"],
            "mediumText" => ["phpType" => "string", "swaggerType" => "string", "example" => $this->wrap_quotes($faker->text()), "fakerMethod" => $bestProp ?? "paragraph"],
            "longText" => ["phpType" => "string", "swaggerType" => "string", "example" => $this->wrap_quotes($faker->text()), "fakerMethod" => $bestProp ?? "text"],
            "json" => ["phpType" => "string", "swaggerType" => "string", "example" => "[]", "fakerMethod" => null],
            "jsonb" => ["phpType" => "string", "swaggerType" => "string", "example" => "[]", "fakerMethod" => null],


            "bool" => ["phpType" => "bool", "swaggerType" => "boolean", "example" => "" . $faker->boolean(), "fakerMethod" => $bestProp ?? "boolean"],
            "boolean" => ["phpType" => "bool", "swaggerType" => "boolean", "example" => "" . $faker->boolean(), "fakerMethod" => $bestProp ?? "boolean"],
            "enum" => ["phpType" => "string", "swaggerType" => "string", "example" => $exampleString, "fakerMethod" => null],
            "set" => ["phpType" => "string", "swaggerType" => "string", "example" => $exampleString, "fakerMethod" => $bestProp ?? "word"],

            "timestamp" => ["phpType" => "\DateTime", "swaggerType" => "string", "example" => $this->wrap_quotes($faker->date), "fakerMethod" => $bestProp ?? "dateTimeThisDecade"],
            "timestampTz" => ["phpType" => "\DateTime", "swaggerType" => "string", "example" => $this->wrap_quotes($faker->date), "fakerMethod" => $bestProp ?? "dateTimeThisDecade"],
            "datetime" => ["phpType" => "\DateTime", "swaggerType" => "string", "example" => $this->wrap_quotes($faker->date), "fakerMethod" => $bestProp ?? "dateTimeThisDecade"],
            "datetimeTz" => ["phpType" => "\DateTime", "swaggerType" => "string", "example" => $this->wrap_quotes($faker->date), "fakerMethod" => $bestProp ?? "dateTimeThisDecade"],
        ][$databaseType] ?? ["phpType" => null, "swaggerType" => null, "example" => "null", "fakerMethod" => null]);

        $this->php = $output['phpType'];
        $this->swagger = $output['swaggerType'];
        $this->example = $output['example'];
        $this->fakerMethod = $output['fakerMethod'];
    }

    private function wrap_quotes($string)
    {
        return '"' . $string . '"';
    }

}
