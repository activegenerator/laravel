<?php
namespace ActiveGenerator\Laravel\Models\Yaml;

use DeepCopy\DeepCopy;

class YamlCollection implements \IteratorAggregate, \JsonSerializable {

  protected array $items = [];

  public function __construct(?array $items = [])
  {
    $this->items = $items ?? [];
  }

  public function getIterator() : YamlIterator
  {
      return new YamlIterator($this);
  }

  public function &iterate()
    {
        foreach ($this->items as &$v) {
            yield $v;
        }
    }

  public function get($position)
  {
      if (isset($this->items[$position])) {
          return $this->items[$position];
      }


      return null;
  }

  public function count() : int
  {
      return count($this->items);
  }

  public function add($item) : YamlCollection
  {
      $class = get_called_class();

      $items = $this->items;
      $items[] = $item;

      return new $class($items);
  }

  public function prepend($item) : YamlCollection
  {
      $class = get_called_class();

      $items = $this->items;

      array_unshift($items, $item);

      return new $class($items);
  }

  public function indexOf($item) {
    return array_search($item, $this->items);
  }

  public function join($glue = ";") : string {
    return join($glue, $this->items);
  }

  public function merge(YamlCollection $items, ?int $atIndex = null) : YamlCollection
  {
      $class = get_called_class();
      $output = $this->items;

      if ($atIndex !== null) {
        array_splice($output, $atIndex, 0, $items->toArray());
      } else {
        $output = array_merge($this->items, $items->toArray());
      }

      return new $class($output);
  }

  public function filter($callback) : YamlCollection
  {
      $class = get_called_class();

      return new $class(array_values(array_filter($this->items, $callback)));
  }

  public function first($callback = null)
  {
      return $callback ? ($this->filter($callback)->first()) : $this->nth(0);
  }

  public function last($callback = null)
  {
      $last = $this->count() - 1;
      return $callback ? ($this->filter($callback)->last()) : $this->nth($last);
  }

  public function nth(int $index)
  {
      return $this->items[$index] ?? null;
  }

  public function any($callback = null) : bool {
    return $callback ? count($this->filter($callback)->toArray()) > 0 : count($this->items) > 0;
  }

  public function toArray() : array {
    return $this->items;
  }

  public function jsonSerialize() : array {
    return $this->toArray();
  }

  public function getByProp(string $prop, string $name) {
    return $this->first(fn($item) => $item->$prop === $name);
  }

  public function select($callback) : YamlCollection {
    return new YamlCollection(array_map($callback, $this->items));
  }

  public function cast($type) {
    return new $type($this->items);
  }

  public function copy() {
    $copier = new DeepCopy();
    return $copier->copy($this);
  }

  public function selectMany($callback) : YamlCollection {
    $collections = array_map($callback, $this->items);
    $output = new YamlCollection();

    foreach($collections as $key => $collection) {

      if (is_subclass_of($collection, YamlCollection::class)) {
        if ($key === 0) {
          // reset the type
          $collectionClass = get_class($collection);
          $output = new $collectionClass;
        }
        $output = $output->merge($collection);
      } else {
        foreach($collection as $item) {
          $output->add($item);
        }
      }
    }

    return $output;
  }

  public function forEach($callback = null)
  {
      foreach($this->items as $item) {
        $callback($item);
      }

      return $this;
  }

  public function unique() {
    return new YamlCollection(array_unique($this->items));
  }

  public function sort($order = "asc") {
      $items = $this->items;

      if ($order == "asc")
        sort($items);
      else
        rsort($items);

      return new YamlCollection($items);
  }
}
