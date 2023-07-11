<?php

namespace Drupal\webhook_receiver;

use Drupal\webhook_receiver\Payload\PayloadInterface;
use Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface;

/**
 * An interface for all WebhookReceiverPlugin type plugins.
 *
 * This is based on code from the Examples module.
 */
interface WebhookReceiverPluginInterface {

  /**
   * Alter a request before it is acted upon.
   *
   * All plugins implementing ::before() should check whether ret['access'] is
   * TRUE before proceeding.
   *
   * @param \Drupal\webhook_receiver\WebhookReceiver $app
   *   The module singleton.
   * @param string $plugin_id
   *   The plugin ID which will process the request.
   * @param string $token
   *   A security token.
   * @param array $ret
   *   A return array which will be stringified and client-facing.
   * @param \Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface $log
   *   A client-facing log.
   * @param \Drupal\webhook_receiver\Payload\PayloadInterface $payload
   *   An array with the "payload" key, and keys for the payload errors and
   *   debug messages.
   * @param bool $simulate
   *   Whether to simulate the request.
   */
  public function before(WebhookReceiver $app, string $plugin_id, string $token, array &$ret, WebhookReceiverLogInterface $log, PayloadInterface $payload, bool $simulate);

  /**
   * Process the payload array, assuming it has been validated.
   *
   * @param \Drupal\webhook_receiver\Payload\PayloadInterface $payload
   *   The payload exactly as it was provided from the requestor.
   * @param \Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface $log
   *   A client-facing log.
   * @param bool $simulate
   *   Whether or nt to simulate to action.
   */
  public function processPayload(PayloadInterface $payload, WebhookReceiverLogInterface $log, bool $simulate);

  /**
   * Validate the payload array.
   *
   * @param \Drupal\webhook_receiver\Payload\PayloadInterface $payload
   *   The payload exactly as it was provided from the requestor.
   * @param \Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface $log
   *   A client-facing log.
   *
   * @return bool
   *   TRUE if valid, FALSE otherwise.
   */
  public function validatePayload(PayloadInterface $payload, WebhookReceiverLogInterface $log) : bool;

}
