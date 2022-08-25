<?php

namespace Drupal\webhook_receiver_defer\WebhookReceiverDeferRequirements;

use Drupal\webhook_receiver_defer\WebhookReceiverDefer;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Check requirements and provide status for this module.
 */
class WebhookReceiverDeferRequirements {

  use StringTranslationTrait;

  /**
   * The injected module singleton.
   *
   * @var \Drupal\webhook_receiver_defer\WebhookReceiverDefer
   */
  protected $module;

  /**
   * Class constructor.
   *
   * @param \Drupal\webhook_receiver_defer\WebhookReceiverDefer $module
   *   The injected module singleton.
   */
  public function __construct(WebhookReceiverDefer $module) {
    $this->module = $module;
  }

  /**
   * Testable implementation of hook_requirements().
   */
  public function hookRequirements(string $phase) : array {
    $requirements = [];
    if ($phase == 'runtime') {
      $requirements += $this->usage();
    }
    return $requirements;
  }

  /**
   * A single requirement lines to check for usage of this module.
   *
   * @return array
   *   An array suitable for consumption by hook_requirements().
   */
  public function usage() : array {
    $requirements['webhook_receiver_db_usage'] = [
      'title' => $this->t('webhook_receiver_defer database usage'),
      'description' => $this->t('webhook_receiver_defer defers processing of webhooks because certain cloud services require an immediate response.'),
      'value' => $this->calcUsage(),
      'severity' => REQUIREMENT_INFO,
    ];
    return $requirements;
  }

  /**
   * Get database usage in the form of a human-readable string.
   *
   * @return string
   *   Database usage in the form of a human-readable string.
   */
  public function calcUsage() : string {
    $all = $this->module->countByType();

    if (!count($all)) {
      return $this->t('There are no requests in the queue.');
    }

    $ret_array = [];

    foreach ($all as $by_type) {
      $ret_array[] = $by_type->status . ': ' . $by_type->count;
    }

    return implode(', ', $ret_array);
  }

}
