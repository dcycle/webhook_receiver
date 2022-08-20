<?php

namespace Drupal\webhook_receiver\WebhookReceiverRequirements;

use Drupal\webhook_receiver\WebhookReceiver;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Check requirements and provide status for this module.
 */
class WebhookReceiverRequirements {

  use StringTranslationTrait;

  /**
   * The injected module singleton.
   *
   * @var \Drupal\webhook_receiver\WebhookReceiver
   */
  protected $module;

  /**
   * Class constructor.
   *
   * @param \Drupal\webhook_receiver\WebhookReceiver $module
   *   The injected module singleton.
   */
  public function __construct(WebhookReceiver $module) {
    $this->module = $module;
  }

  /**
   * Testable implementation of hook_requirements().
   */
  public function hookRequirements(string $phase) : array {
    $requirements = [];
    if ($phase == 'runtime') {
      $requirements += $this->endpoints();
    }
    return $requirements;
  }

  /**
   * A single requirement lines to check for endpoints.
   *
   * @return array
   *   An array suitable for consumption by hook_requirements().
   */
  public function endpoints() : array {
    $num_endpoints = count($this->module->webhooks());
    $requirements['webhook_receiver_endpoints'] = [
      'title' => $this->t('Number of webhook_receiver endpoints'),
      'description' => $num_endpoints ? $this->t('There is at least one webhook_receiver endpoint. You can see all endpoints by running webhook_receiver()->webhooks().') : $this->t('There are no webhook_receiver endpoints. You should either uninstall webhook_receiver, or add endpoints by following the instructions at https://github.com/dcycle/webhook_receiver.'),
      'value' => $num_endpoints,
      'severity' => $num_endpoints ? REQUIREMENT_INFO : REQUIREMENT_WARNING,
    ];
    return $requirements;
  }

}
