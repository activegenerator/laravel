<?php

namespace ActiveGenerator\Laravel\Models\Yaml;


// Create an Iterator for User
class YamlIterator implements \Iterator
{
    private $position = 0;

    private $collection;

    public function __construct(YamlCollection $collection)
    {
        $this->collection = $collection;
    }

    public function current()
    {
        return $this->collection->get($this->position);
    }

    public function next()
    {
        $this->position++;
    }

    public function key() : int
    {
        return $this->position;
    }

    public function valid() : bool
    {
        return !is_null($this->collection->get($this->position));
    }

    public function rewind()
    {
        $this->position = 0;
    }
}
