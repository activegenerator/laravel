<?php

namespace ActiveGenerator\Laravel\Helpers;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class Files {

	public static function regex($folder, $pattern) {
    $dir = new RecursiveDirectoryIterator($folder);
    $ite = new RecursiveIteratorIterator($dir);
    $files = new RegexIterator($ite, $pattern, RegexIterator::MATCH);

    foreach($files as $file) {
         yield $file->getPathName();
    }
}
}
