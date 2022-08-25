<?php

namespace Drupal\webhook_receiver\Payload;

/**
 * Represents a payload, which is received by the webhook caller.
 */
interface PayloadInterface {

  /**
   * Get the number of errors in this payload.
   *
   * @return int
   *   The number of errors in this payload.
   */
  public function numErrors() : int;

  /**
   * Get errors in this payload as an array.
   *
   * @return array
   *   Errors in this payload as an array.
   */
  public function errorsAsArray() : array;

  /**
   * Get this payload as a JSON string.
   *
   * @return string
   *   This payload as a JSON string.
   */
  public function asString() : string;

  /**
   * Get a value from the payload.
   *
   * For example:
   * If the pathload is ['a' => ['b' => TRUE]], we will return TRUE.
   * If the pathload is ['a' => ['b' => FALSE]], we will return FALSE.
   * If the pathload is [], we will return the default.
   *
   * @param array $path
   *   A path, for example ['a', 'b'].
   * @param mixed $default
   *   The default value if the path does not exist.
   *
   * @return mixed
   *   The value of this path.
   */
  public function get(array $path, $default);

  /**
   * Like ::get() for boolean values.
   */
  public function getBool(array $path, bool $default = FALSE) : bool;

  /**
   * Like ::get() for string values.
   */
  public function getString(array $path, string $default = '') : string;

  /**
   * Validates a value from the callback.
   *
   * The the path does not exist, return FALSE.
   *
   * For example:
   * If the pathload is ['a' => ['b' => 'hello']], we will return TRUE.
   *
   * @param array $path
   *   A path, for example ['a', 'b'].
   * @param callable $callback
   *   A callable to call on the value of 'b' ('hello' in this example).
   *
   * @return bool
   *   TRUE if this validates.
   */
  public function validatePath(array $path, callable $callback) : bool;

  /**
   * Unset a value in this payload.
   *
   * For example:
   * We want to unset ['a' => ['b' => 'this should be unset']].
   *
   * @param array $path
   *   A path to unset, for example ['a', 'b'].
   */
  public function unset(array $path);

}
