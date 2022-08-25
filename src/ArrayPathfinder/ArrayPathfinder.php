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
  public function validatePath(array $array, array $path, callable $callback) : bool {
    throw new \Exception('nyi');
  }

  /**
   * {@inheritdoc}
   */
  public function get(array $array, array $path, $default) {
    throw new \Exception('nyi');
  }

}
