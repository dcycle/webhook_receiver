<?php

namespace Drupal\webhook_receiver\WebhookReceiverActivityLog;

/**
 * A log whose results are logged to the watchdog.
 */
interface WebhookReceiverActivityLogInterface {

  /**
   * Log an error to the watchdog.
   *
   * @param \Throwable $throwable
   *   A throwable to log.
   *
   * @return string
   *   An error ID.
   */
  public function logThrowable(\Throwable $throwable) : string;

}
