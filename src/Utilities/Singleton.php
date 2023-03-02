<?php

namespace Drupal\webhook_receiver\Utilities;

/**
 * Implements the Singleton design pattern.
 */
trait Singleton {

  /**
   * Interal instance variable used with the instance() method.
   *
   * @var object|null
   */
  static private $instance;

  /**
   * Implements the Singleton design pattern.
   *
   * Only one instance of the Concord class should exist per execution.
   *
   * @return mixed
   *   The single instance of the class using the Singleton trait. (PHP does
   *   not at this type allow type-hinting traits, which is why this is mixed.)
   */
  public static function instance() {
    // See http://stackoverflow.com/questions/15443458
    $class = get_called_class();

    // Not sure why the linter tells me $instance is not used.
    // @codingStandardsIgnoreStart
    if (!$class::$instance) {
    // @codingStandardsIgnoreEnd
      $class::$instance = new $class();
    }
    return $class::$instance;
  }

}
