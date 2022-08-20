<?php

namespace Drupal\webhook_receiver;

use Drupal\Component\Plugin\PluginBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\webhook_receiver\WebhookReceiver;

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
  public function before(WebhookReceiver $app, string $plugin_id, string $token, &$ret, $log) {
    // Do nothing.
  }

}
