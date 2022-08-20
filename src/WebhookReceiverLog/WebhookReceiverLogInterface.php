<?php

namespace Drupal\webhook_receiver\WebhookReceiverLog;

/**
 * A log whose results are shown as a result to the webhook caller.
 */
interface WebhookReceiverLogInterface {

  /**
   * Log an error to be displayed as part of the webhook response.
   *
   * @param string $string
   *   A string to log.
   */
  public function err(string $string);

  /**
   * Log a message to be displayed as part of the webhook response.
   *
   * @param string $string
   *   A string to log.
   */
  public function debug(string $string);

  /**
   * Output the results of this log as an array.
   *
   * @return array
   *   An array with "errors" and "debug".
   */
  public function toArray() : array;

}
