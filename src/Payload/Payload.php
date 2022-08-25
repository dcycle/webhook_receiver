<?php

namespace Drupal\webhook_receiver\Payload;

use Drupal\webhook_receiver\ArrayPathfinder\ArrayPathfinderInterface;
use Drupal\Component\Serialization\Json;

/**
 * Represents a payload, which is received by the webhook caller.
 */
class Payload implements PayloadInterface {

  /**
   * The payload as a string.
   *
   * @var string
   */
  protected $payloadString;

  /**
   * The errors associated with this payload.
   *
   * @var array
   */
  protected $errors;

  /**
   * The debug messages associated with this payload.
   *
   * @var array
   */
  protected $debug;

  /**
   * The array pathfinder service.
   *
   * @var \Drupal\webhook_receiver\ArrayPathfinder\ArrayPathfinderInterface
   */
  protected $arrayPathfinder;

  /**
   * Constructor.
   *
   * @param string $payload_string
   *   The payload as a string.
   * @param \Drupal\webhook_receiver\ArrayPathfinder\ArrayPathfinderInterface $array_pathfinder
   *   The array pathfinder service.
   */
  public function __construct(string $payload_string, ArrayPathfinderInterface $array_pathfinder) {
    $this->payloadString = $payload_string;
    $this->arrayPathfinder = $array_pathfinder;
    $this->errors = [];
    $this->debug = [];
  }

  /**
   * {@inheritdoc}
   */
  public function numErrors() : int {
    return count($this->errorsAsArray());
  }

  /**
   * {@inheritdoc}
   */
  public function errorsAsArray() : array {
    // Create an array, which might log some errors.
    $this->toArray();
    return $this->errors;
  }

  /**
   * {@inheritdoc}
   */
  public function asString() : string {
    return $this->payloadString;
  }

  /**
   * {@inheritdoc}
   */
  public function get(array $path, $default) {
    return $this->arrayPathfinder->get($this->toArray(), $path, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function getBool(array $path, bool $default = FALSE) : bool {
    return $this->get($path, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function getString(array $path, string $default = '') : string {
    return $this->get($path, $default);
  }

  /**
   * {@inheritdoc}
   */
  public function unset(array $path) {
    $array = $this->toArray();
    $this->arrayPathfinder->unset($array, $path);
    $this->fromArray($array);
  }

  /**
   * {@inheritdoc}
   */
  public function validatePath(array $path, $default, callable $callback) : bool {
    return $this->arrayPathfinder->validatePath($this->toArray(), $path, $default, $callback);
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() : array {
    $ret = Json::decode($this->payloadString);

    if ($this->payloadString && !$ret) {
      $err = 'The payload string is not empty yet we could not decocde it with JSON. Is it valid JSON?';
      $this->errors[$err] = $err;
      $ret = [];
    }

    if (!is_array($ret)) {
      $debug = 'The payload string resolved to a non-array; this is not supported and it will be made into an array';
      $this->debug[$debug] = $debug;
      $ret = [$ret];
    }

    return $ret;
  }

  /**
   * {@inheritdoc}
   */
  public function fromArray(array $array) {
    $this->payloadString = Json::encode($array);
  }

}
