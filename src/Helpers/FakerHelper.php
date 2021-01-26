<?php

namespace ActiveGenerator\Laravel\Helpers;

use ActiveGenerator\Laravel\Models\Yaml\YamlField;
use Illuminate\Support\Str;

function wrap_quotes($string) {
    return '"' . $string . '"';
}

class FakerHelper {
    /**
     * Get the available properties on the faker class
     *
     * @return array
     */
    public static function getFakerProps() : array {
      $rc = new \ReflectionClass('\Faker\Generator');
      $doc = $rc->getDocComment();

      preg_match_all('/@property string \$(.*)/', $doc , $output_array);

      return $output_array[1];
    }

    /**
     * Get the best faker property for the field
     *
     * @param BaseField $field
     */
    public static function getBestFakerPropertyForSlug(string $slug, $fallback = null) : ?string {
      $props = self::getFakerProps();
      $matches = [];


      $override = [
        'lat' => 'latitude',
        'lng' => 'longitude',
        'title' => 'sentence'
      ];

      if( !empty($override[$slug]) ) {
        return $override[$slug];
      }

      foreach ($props as $prop) {
        $a = strtolower($prop);
        $b = str_replace("_", "", strtolower($slug));

        if ($a == $b) {
          array_unshift($matches, $prop);
          continue;
        }

        if ( (Str::length($a) >= 4 && str_contains($b, $a)) ||
          (Str::length($b) >= 4 && str_contains($a, $b)) ) {
          $matches[] = $prop;
          continue;
        }
      }

      return $matches[0] ?? $fallback;
    }
}
