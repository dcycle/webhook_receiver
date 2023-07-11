<?php

namespace Drupal\webhook_receiver\Plugin\WebhookReceiverPlugin;

use Drupal\webhook_receiver\Payload\PayloadInterface;
use Drupal\webhook_receiver\WebhookReceiver;
use Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface;
use Drupal\webhook_receiver\WebhookReceiverPluginBase;

/**
 * Checks the token for all webhooks.
 *
 * @WebhookReceiverPluginAnnotation(
 *   id = "webhook_receiver_check_token",
 *   hide = true,
 *   description = @Translation("Checks the token for all webhooks."),
 *   weight = -100,
 *   examples = {
 *   },
 * )
 */
class CheckToken extends WebhookReceiverPluginBase {

  /**
   * {@inheritdoc}
   */
  public function before(WebhookReceiver $app, string $plugin_id, string $token, array &$ret, WebhookReceiverLogInterface $log, PayloadInterface $payload, bool $simulate) {
    if ($app->webhookReceiverSecurity()->token($plugin_id) != $token) {
      $log->debug('The token is invalid, access is denied.');
      $ret['access'] = FALSE;
    }
    else {
      $ret['access'] = TRUE;
      $log->debug('The token is valid, moving on.');
    }
  }

}
