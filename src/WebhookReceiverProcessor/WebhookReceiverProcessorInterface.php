<?php

namespace Drupal\webhook_receiver\WebhookReceiverProcessor;

use Drupal\webhook_receiver\WebhookReceiver;

interface WebhookReceiverProcessorInterface {

  /**
   * Process a request.
   *
   * @param \Drupal\webhook_receiver\WebhookReceiver $app
   *   A webhookReceiver service.
   * @param string $plugin_id
   *   A plugin ID.
   * @param string $token
   *   A token.
   * @param bool $simulate
   *   Whether to simulate the request.
   * @param array $payload
   *   An array with the keys: 'payload' for the payload itself, and
   *   'payload_errors' for any errors which should trigger a 500 error,
   *   'payload_notices' for any notices which have no effect on the response
   *   code.
   *
   * @return array
   *   A response for output as JSON.
   */
  public function process(WebhookReceiver $app, string $plugin_id, string $token, bool $simulate, array $payload) : array;

}
