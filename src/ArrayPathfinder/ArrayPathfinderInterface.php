<?php

namespace Drupal\webhook_receiver\ArrayPathfinder;

/**
 * Utility to find nested items in associative arrays.
 */
interface ArrayPathfinderInterface {

  /**
   * Unset a value in an array.
   *
   * For example:
   * We want to unset ['a' => ['b' => 'this should be unset']].
   *
   * @param array $array
   *   An array on which to work.
   * @param array $path
   *   A path to unset, for example ['a', 'b'].
   */
  public function unset(array &$array, array $path);

  /**
   * Get a value in an array.
   *
   * For example:
   * We want to unset ['a' => ['b' => 'this should be returned']].
   *
   * @param array $array
   *   An array on which to work.
   * @param array $path
   *   A path to unset, for example ['a', 'b'].
   * @param mixed $default
   *   A default value to use.
   *
   * @return mixed
   *   The value.
   */
  public function get(array $array, array $path, $default);

  /**
   * Validates a value from the callback.
   *
   * The the path does not exist, return FALSE.
   *
   * For example:
   * If the pathload is ['a' => ['b' => 'hello']], we will return TRUE.
   *
   * @param array $array
   *   An array to check.
   * @param array $path
   *   A path, for example ['a', 'b'].
   * @param callable $callback
   *   A callable to call on the value of 'b' ('hello' in this example).
   *
   * @return bool
   *   TRUE if this validates.
   */
  public function validatePath(array $array, array $path, callable $callback) : bool;

}
