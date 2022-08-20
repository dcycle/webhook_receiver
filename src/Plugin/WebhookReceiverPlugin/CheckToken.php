<?php

namespace Drupal\webhook_receiver\Plugin\WebhookReceiverPlugin;

use Drupal\webhook_receiver\WebhookReceiverPluginBase;
use Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\webhook_receiver\WebhookReceiver;

/**
 * Very simple example of the webhook receiver module which logs the payload.
 *
 * @WebhookReceiverPluginAnnotation(
 *   id = "webhook_receiver_check_token",
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
  public function before(WebhookReceiver $app, string $plugin_id, string $token, &$ret, $log) {
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
