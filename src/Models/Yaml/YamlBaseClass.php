<?php

namespace ActiveGenerator\Laravel\Models\Yaml;

use ActiveGenerator\Laravel\BaseClass;
use Illuminate\Support\Arr;

/**
 *
 */
class YamlBaseClass extends BaseClass {
  public function __construct()
  {
    parent::__construct();
  }

  public function get($query, $default = '') {
    return Arr::get($this->data, $query, $default);
  }
}
