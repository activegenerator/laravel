<?php

namespace ActiveGenerator\Laravel\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class BeforeSchemaTablesRegex
{
    use Dispatchable, SerializesModels;

    public string $regex;

    public function __construct(string &$regex)
    {
        $this->regex = &$regex;
    }
}
