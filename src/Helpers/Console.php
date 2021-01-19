<?php

namespace ActiveGenerator\Laravel\Helpers;

class Console {

	var $margin = "  ";

	public static function parseColors(string $string) : string {
		$string = str_replace("[red]", "\033[31m", $string);
		$string = str_replace("[/red]", "\033[39m", $string);

		$string = str_replace("[cyan]", "\033[36m", $string);
		$string = str_replace("[/cyan]", "\033[39m", $string);

		$string = str_replace("[default]", "\033[39m", $string);
		$string = str_replace("[/default]", "\033[39m", $string);

		return $string;
  }

  public static function parse(string $string): string {
    $string = self::parseColors($string);

    return $string;
  }

	public static function line(?string $msg = null) {
    $msg = self::parse($msg);
		\cli\line($msg);
  }

  public static function err(?string $msg = null) {
    $msg = self::parse($msg);
		\cli\err($msg);
  }

  public static function prompt(string $question, bool $default = false, string $marker = ':') {
    $question = self::parse($question);
		\cli\prompt($question, $default = false, $marker = ':');
  }

  public static function choose(string $question, string $choices = 'yn', string$default = 'n') {
    $question = self::parse($question);
		\cli\choose($question, $choices, $default);
	}

}
