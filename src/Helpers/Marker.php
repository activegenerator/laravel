<?php

namespace ActiveGenerator\Laravel\Helpers;

use Generator;
use Illuminate\Support\Str;

class Marker {
  public string $mark;

  public function __construct(string $mark) {
    $this->mark = $mark;
  }

  public function phpComment($str) {
      return '// ' . $str . '';
  }

  public function htmlComment($str) {
    return '<!-- ' . $str . ' -->';
  }

  public function bladeComment($str) {
    return '{{-- ' . $str . ' --}}';
  }

  public function line($type = "php") {
    $mark = "@gen " . $this->mark . " - do not remove";

    if ($type === "php") return $this->phpComment($mark);
    if ($type === "html") return $this->htmlComment($mark);
    if ($type === "blade") return $this->bladeComment($mark);
  }
  public function start($type = "php") {
    $mark = "@gen:start " . $this->mark . " - do not remove";

    if ($type === "php") return $this->phpComment($mark);
    if ($type === "html") return $this->htmlComment($mark);
    if ($type === "blade") return $this->bladeComment($mark);
  }
  public function end($type = "php") {
    $mark = "@gen:end " . $this->mark . " - do not remove";

    if ($type === "php") return $this->phpComment($mark);
    if ($type === "html") return $this->htmlComment($mark);
    if ($type === "blade") return $this->bladeComment($mark);
  }

  public static function hasMark(string $string, string $mark, string $type = ":start|:end|") : bool {
    return count(preg_grep('/@gen(' . $type . ') ' . $mark . '/', explode(PHP_EOL, $string))) > 0;
  }

  public static function removeLineMark(string $string, string $mark) {
    return preg_replace("/\r?\n(?!\r?\n).*(\/\/|<!--|{{--) @gen " . $mark . ".*/i", "", $string);
  }

  public static function removeMark(string $string, string $mark) {
    return preg_replace("/\r?\n(?!\r?\n).*(\/\/|<!--|{{--) @gen:start " . $mark . "[\S\s]*?(\/\/|<!--|{{--) @gen:end " . $mark . ".*/i", "", $string);
  }

  public static function getStartMarkLineNumber(string $string, string $mark) : ?int {
    return self::getMarkLineNumber($string, $mark, ":start");
  }

  public static function getEndMarkLineNumber(string $string, string $mark) : ?int {
    return self::getMarkLineNumber($string, $mark, ":end");
  }

  public static function getMarkLineNumber(string $string, string $mark, string $type = ":start|:end|") : ?int {
    $lines = explode(PHP_EOL, $string);

    foreach($lines as $num => $line) {
      if (self::hasMark($line, $mark, $type)) {
        return $num;
      }
    }

    return null;
  }
}
