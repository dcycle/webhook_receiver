<?php

namespace Drupal\webhook_receiver;

use Symfony\Component\HttpFoundation\Request;
use Drupal\webhook_receiver\WebhookReceiver;

/**
 * An interface for all WebhookReceiverPlugin type plugins.
 *
 * This is based on code from the Examples module.
 */
interface WebhookReceiverPluginInterface {

  public function before(WebhookReceiver $app, string $plugin_id, string $token, &$ret, $log);

}
