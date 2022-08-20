<?php

namespace Drupal\webhook_receiver\Utilities;

use Drupal\Core\Access\AccessResult;
// @codingStandardsIgnoreStart
use Drupal\webhook_receiver\WebhookReceiver;
// @codingStandardsIgnoreEnd

/**
 * A quick way to fetch mockable service singletons.
 */
trait Mockables {

  /**
   * Mockable wrapper around AccessResult::allowed().
   */
  public function accessAllowed() {
    return AccessResult::allowed();
  }

  /**
   * Mockable wrapper around AccessResult::forbidden().
   */
  public function accessForbidden() {
    return AccessResult::forbidden();
  }

}
