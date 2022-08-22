<?php

namespace Drupal\webhook_receiver;

use Drupal\Component\Plugin\PluginBase;
use Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface;

/**
 * A base class to help developers implement WebhookReceiverPlugin objects.
 *
 * @see \Drupal\webhook_receiver\Annotation\WebhookReceiverPluginAnnotation
 * @see \Drupal\webhook_receiver\WebhookReceiverPluginInterface
 */
abstract class WebhookReceiverPluginBase extends PluginBase implements WebhookReceiverPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function before(WebhookReceiver $app, string $plugin_id, string $token, array &$ret, WebhookReceiverLogInterface $log) {
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function processPayloadArray(array $payload, WebhookReceiverLogInterface $log, bool $simulate) {
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function validatePayloadArray(array $payload, WebhookReceiverLogInterface $log) : bool {
    $log->debug('Setting the payload to invalid. Subclasses such as the example ./modules/webhook_receiver_example/src/Plugin/WebhookReceiverPlugin/LogPayload.php should override and return TRUE if the payload is as expected.');
    return FALSE;
  }

}
