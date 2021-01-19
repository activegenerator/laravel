<?php

namespace ActiveGenerator\Laravel\Events;

use ActiveGenerator\Laravel\Collections\TableCollection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class AfterSchemaParse
{
    use Dispatchable, SerializesModels;

    public TableCollection $tableCollection;
    public string $schemaString;

    public function __construct(TableCollection &$tableCollection, string $schemaString)
    {
        $this->tableCollection = &$tableCollection;
        $this->schemaString = &$schemaString;
    }
}
