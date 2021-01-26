<?php
namespace ActiveGenerator\Laravel\Generators\Base;

use ActiveGenerator\Laravel\BaseClass;
use ActiveGenerator\Laravel\Models\Table;
use ActiveGenerator\Laravel\Helpers\Marker;
use ActiveGenerator\Laravel\Helpers\PolyVar;
use ActiveGenerator\Laravel\Helpers\Template;
use ActiveGenerator\Laravel\Models\Yaml\YamlModel;
use ActiveGenerator\Laravel\Traits\ClassNameTrait;
use Error;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ParseError;

abstract class Generator extends BaseClass {

  public string $class;
  public bool $runningInConsole;

  public array $vars = [];

  public string $defaultPrepend = "<?php" . PHP_EOL . PHP_EOL;
  public string $defaultAppend= "";

  public Filesystem $fs;
  public Command $command;

  public array $currentDefinition = [];

  /**
   * Constructor
   *
   * @param array $yaml
   * @param array $data Environment data
   * @param integer $index
   */
  public function __construct(YamlModel $model, array $context = [], int $index = 0)
  {
    parent::__construct();

    $this->yaml = $model;
    $this->vars['yaml'] = $model;
    $this->vars['marker'] = new Marker($model->name);
    $this->vars['appNamespace'] = substr($context['namespace'] ?? '', 0, -1);
    $this->vars['outputDir'] = $context['outputDir'] ?? '';
    $this->vars['userTemplatesDir'] = $context['userTemplatesDir'] ?? '';
    $this->vars['basePath'] = base_path();
    $this->vars['appPath'] = app_path();
    $this->vars['relativeAppPath'] = str_replace($this->vars['basePath'], "", $this->vars['appPath']);
    $this->vars['configPath'] = config_path();
    $this->vars['publicPath'] = public_path();
    $this->vars['storagePath'] = storage_path();
    $this->vars['date'] = date('Y_m_d_His'); // 2020_10_04_120224
    $this->vars['index'] = $index;

    $this->extend();
  }

  /**
   * Undocumented function
   *
   * @param string $key
   * @param [type] $default
   */
   public function var($query, $default = null) {
    return Arr::get($this->vars, $query, $default);
   }

  /**
   * This is run after the construct and can be used to extend the base
   *
   * @return void
   */
  public function extend() {}

  /**
   * Get the template dir of the plugin for calculating the relativeTemplate later
   *
   * @return string
   */
  public abstract function templatesDir() : string;

  /**
   * Should return an array with keys: template, output
   *
   * @return array
   */
  public abstract function output() : array;

  /**
   * Undocumented function
   *
   * @return void
   */
  public function generate() {
    $definition = $this->output();
    $definitions = isset($definition['template']) ? [$definition] : $definition;

    foreach($definitions as &$definition) {
        $this->vars['marker'] = new Marker($this->yaml->name . " " . basename($definition['template']));

        $outputPath = Template::compile($definition['output'], $this->vars);
        $outputPath = preg_replace("/\/\/+/", "/", $outputPath);
        // $this->command->info($this->class);

        $contents = $this->render($definition);
        $definition['rendered'] = $contents;
        $definition['outputPath'] = $outputPath;

        if ($contents === null) continue;

        $outputMinusBase = Str::replaceFirst(base_path() . "/", "", $outputPath);

        $this->command->line($this->class . " => \033[32m" . $outputMinusBase . "\033[39m");

        $this->write($outputPath, $contents);
    }

    return $definitions;
  }

  /**
   * Get the relative directory of the template as deep as nescassary
   *
   * @param [type] $definition
   * @return void
   */
  public function getRelativeTemplate($definition): string {
    return str_replace($this->templatesDir(), "", $definition['template']);
  }

  /**
   * Undocumented function
   *
   * @param [type] $definition
   * @return string
   */
  public function getUserTemplate($definition): string
  {
    return realpath($this->var('userTemplatesDir') . '/' . $this->class . '/' . $this->getRelativeTemplate($definition));
  }

  public static function onAllGenerated($renderedItems) {

  }

  /**
   * Undocumented function
   *
   * @param [type] $definition
   * @return string|null
   */
  public function render($definition) : ?string {
    $userTemplate = $this->getUserTemplate($definition);
    $templatePath = File::exists($userTemplate) ? $userTemplate : $definition['template'];

    $template = Template::get($templatePath);

    if (!$template) return null;

    try {
    $output =  ($definition['prepend'] ?? $this->defaultPrepend) . Template::compile($template, $this->vars) . ($definition['append'] ?? $this->defaultAppend);
    } catch(Exception $err) {
        // dd($err);
        $this->command->error("Error in template '" . $templatePath . "'");
        $this->command->error($err->getMessage());
        exit();
    } catch(Error $err) {
        // dd($err);
        $this->command->error("Error in template '" . $templatePath . "'");
        $this->command->error($err->getMessage());
        exit();
    }

    return $output;
  }

  /**
   * Undocumented function
   *
   * @param string $filename
   * @param string $contents
   * @return void
   */
  public function write(string $filename, string $contents) {
    $this->put($filename, $contents);
  }

  /**
   * Insert a line before/after a certain regex or replace when marker is present.
   * If regex is null the fragment is appended to the file.
   *
   * @param string $filename
   * @param string $contents
   * @param string|null $regex
   * @param boolean $insertBefore
   * @return void
   */
  public function insert(string $filename, string $contents, ?string $regex = null, bool $insertBefore = true, string $mode = 'first') {
    $contents = static::insertText($filename, $contents, $this->var('marker')->mark, $regex, $insertBefore, $mode);

    $this->put($filename, $contents);
  }

  /**
   * Insert a line before a certain regex or replace when marker is present
   *
   * @param string $filename
   * @param string $contents
   * @param string $regex
   * @return void
   */
  public function insertBefore(string $filename, string $contents, string $regex, $mode = 'first') {
    return $this->insert($filename, $contents, $regex, true, $mode);
  }

  /**
   * Insert a line after a certain regex or replace when marker is present
   *
   * @param string $filename
   * @param string $contents
   * @param string $regex
   * @return void
   */
  public function insertAfter(string $filename, string $contents, string $regex, $mode = 'first') {
    return $this->insert($filename, $contents, $regex, false, $mode);
  }

  /**
   * Write the contents to disk
   *
   * @param string $filename
   * @param string $contents
   * @return void
   */
  protected function put(string $filename, string $contents) {
    if (File::exists($filename) && !$this->command->option('force')) {
      $answer = "?";
      while($answer != "y" && $answer != "n") {
        if (!$this->runningInConsole) {
          $answer = "n";

          $this->command->info("File exists, select 'overwrite' to overwrite this file.");

          continue;
        }
        $answer = strtolower($this->command->ask("Do you want to overwrite $filename? (Y/n)"));
        if ($answer === "") {
          $answer = "y";
        }
      }
    } else {
      $answer = "y";
    }

    if ($answer === "y") {
      File::ensureDirectoryExists(File::dirname($filename));
      File::put($filename, $contents);
    }
  }


  public static function insertText(string $filename, string $contents, string $mark, ?string $regex = null, bool $insertBefore = true, string $mode = 'first') {
   try {
     $contents = trim($contents, "\n\r");
     $changedFile = File::get($filename);

     $changedFileLines = explode(PHP_EOL, $changedFile);

     $start = Marker::getStartMarkLineNumber($changedFile, $mark);
     $end = Marker::getEndMarkLineNumber($changedFile, $mark);

     // The marker is found, let's replace this
     if ($start !== null && $end !== null) {
       $contentsLines = explode(PHP_EOL, $contents);
       array_splice($changedFileLines, $start, $end - $start + 1, $contentsLines);

       $contents = implode(PHP_EOL, $changedFileLines);
     } else {
       // Replace before/after regex
       if ($regex !== null) {
         $found = preg_match_all($regex, $changedFile, $matches, PREG_OFFSET_CAPTURE);

         if ($found) {
           foreach($matches[0] ?? [] as $index => $match) {
             if ($mode == 'last' && count($matches[0]) - 1 !== $index) continue;
             if ($mode == 'first' && $index > 0) continue;

             $matchValue = $match[0];
             $matchChar = $match[1];

             $before = $insertBefore ? substr($changedFile, 0, $matchChar) : substr($changedFile, 0, $matchChar + strlen($matchValue) + 1);
             $after = $insertBefore ? substr($changedFile, $matchChar) : substr($changedFile, $matchChar + strlen($matchValue) + 1);

             $contents = $before . PHP_EOL . $contents . PHP_EOL . $after;
           }
         } else {
           // Not found? Append
           $contents = $changedFile . PHP_EOL . $contents;
         }
       } else {
         // No regex? Append
         $contents = $changedFile . PHP_EOL . $contents;
       }
     }
   } catch(FileNotFoundException $ex) {}

   return $contents;
 }
}
