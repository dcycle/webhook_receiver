<?php

namespace Drupal\webhook_receiver\ArrayPathfinder;

/**
 * Utility to find nested items in associative arrays.
 */
class ArrayPathfinder implements ArrayPathfinderInterface {

  /**
   * {@inheritdoc}
   */
  public function unset(array &$array, array $path) {
    $first = array_shift($path);

    if (array_key_exists($first, $array)) {
      if (count($path)) {
        $this->unset($array[$first], $path);
      }
      else {
        unset($array[$first]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validatePath(array $array, array $path, $default, callable $callback) : bool {
    return $callback($this->get($array, $path, $default));
  }

  /**
   * {@inheritdoc}
   */
  public function get(array $array, array $path, $default) {
    $first = array_shift($path);

    if (array_key_exists($first, $array)) {
      if (count($path)) {
        return $this->get($array[$first], $path, $default);
      }
      else {
        return $array[$first];
      }
    }

    return $default;
  }

}
