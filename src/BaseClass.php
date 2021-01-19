<?php

namespace ActiveGenerator\Laravel;

use ActiveGenerator\Laravel\Traits\ClassNameTrait;

/**
 *
 */
class BaseClass {
  public string $class;

  public function __construct()
  {
    $this->class = $this->getClassName();
  }

  public function getClassName($namespace = false) {
    $reflection = (new \ReflectionClass($this));

    return $namespace ? $reflection->getName() : $reflection->getShortName();
  }
}
