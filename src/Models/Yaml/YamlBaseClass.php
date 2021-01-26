<?php

namespace ActiveGenerator\Laravel\Models\Yaml;

use ActiveGenerator\Laravel\BaseClass;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 *
 */
class YamlBaseClass extends BaseClass {
  public function __construct()
  {
    parent::__construct();
  }

  public function get($query, $default = '') {
    if (!str_contains($query, ".") && isset($this->{$query}))
        return $this->{$query};

    return Arr::get($this->data, $query, $default);
  }

  public function str($query, $transform = '', $default = '')
  {
    //   if ($query === "foreignId") {
    //       var_dump($query, $transform);
    //   }
      $item = $this->get($query, $default);
      if (!$item) return $default;

      return Str::to($item, $transform);
  }
}
