<?php

namespace ActiveGenerator\Laravel\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class BeforeSchemaStringParse
{
    use Dispatchable, SerializesModels;

    public string $schemaString;

    public function __construct(string &$schemaString)
    {
        $this->schemaString = &$schemaString;
    }
}
