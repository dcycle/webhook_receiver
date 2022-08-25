<?php

namespace Drupal\webhook_receiver\SelfTest;

/**
 * Allows logging during self-tests.
 */
trait SelfTestLogTrait {

  /**
   * Log an error and exit with an error exit code.
   *
   * @param mixed $mixed
   *   Anything that's loggable.
   */
  public function err($mixed) {
    $this->log(' --- ERROR --- ');
    $this->log($mixed);
    die();
  }

  /**
   * Log a message.
   *
   * @param mixed $mixed
   *   Anything that's loggable.
   */
  public function log($mixed) {
    if (is_string($mixed)) {
      print($mixed . PHP_EOL);
    }
    else {
      print_r($mixed);
    }
  }

}
