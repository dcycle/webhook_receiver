<?php

namespace Drupal\webhook_receiver;

use Drupal\webhook_receiver\WebhookReceiverSecurity\WebhookReceiverSecurity;
use Drupal\webhook_receiver\WebhookReceiverProcessor\WebhookReceiverProcessorInterface;
use Drupal\Component\Serialization\Json;

/**
 * WebhookReceiver singleton. Use \Drupal::service('webhook_receiver').
 */
class WebhookReceiver {

  /**
   * The injected webhook receiver security service.
   *
   * @var \Drupal\webhook_receiver\WebhookReceiverSecurity\WebhookReceiverSecurity
   */
  protected $webhookReceiverSecurity;

  /**
   * The injected webhook receiver request processor.
   *
   * @var \Drupal\webhook_receiver\WebhookReceiverProcessor\WebhookReceiverProcessorInterface
   */
  protected $processor;

  /**
   * Get the injected security service.
   *
   * @return \Drupal\webhook_receiver\WebhookReceiverSecurity\WebhookReceiverSecurity
   *   The injected security service.
   */
  public function webhookReceiverSecurity() : WebhookReceiverSecurity {
    return $this->webhookReceiverSecurity;
  }

  /**
   * Constructs a new WebhookReceiver object.
   *
   * @param \Drupal\webhook_receiver\WebhookReceiverSecurity\WebhookReceiverSecurity $webhookReceiverSecurity
   *   An injected webhook receiver security service.
   * @param \Drupal\webhook_receiver\WebhookReceiverProcessor\WebhookReceiverProcessorInterface $processor
   *   An injected webhook receiver processor service.
   */
  public function __construct(WebhookReceiverSecurity $webhookReceiverSecurity, WebhookReceiverProcessorInterface $processor) {
    $this->webhookReceiverSecurity = $webhookReceiverSecurity;
    $this->processor = $processor;
  }

  /**
   * Get all WebhookReceiverPlugin plugins.
   *
   * See the included expose_status_ignore module for an example of how to
   * create a Plugin.
   *
   * @return WebhookReceiverPluginCollection
   *   All plugins.
   *
   * @throws \Exception
   */
  public function plugins() : WebhookReceiverPluginCollection {
    return WebhookReceiverPluginCollection::instance();
  }

  /**
   * Process a request.
   *
   * @param string $plugin_id
   *   A plugin ID.
   * @param string $token
   *   A token.
   * @param bool $simulate
   *   Whether to simulate the action.
   * @param string $payload
   *   The payload as a string.
   *
   * @return array
   *   A response for output as JSON.
   */
  public function process(string $plugin_id, string $token, bool $simulate, string $payload) : array {
    $payload_array = $this->payload($payload);
    return $this->processor->process($this, $plugin_id, $token, $simulate, $payload_array);
  }

  /**
   * Get the payload as an array.
   *
   * @param string $strigified_payload
   *   The payload as a string.
   *
   * @return array
   *   An array with the keys: 'payload' for the payload itself, and
   *   'payload_errors' for any errors which should trigger a 500 error,
   *   'payload_notices' for any notices which have no effect on the response
   *   code.
   */
  public function payload(string $strigified_payload) : array {
    $ret = [
      'payload_errors' => [],
      'payload_notices' => [],
    ];

    if (!$strigified_payload) {
      $ret['payload_notices'][] = 'The stringified payload computes as empty';
    }

    $ret['payload'] = Json::decode($strigified_payload);

    if ($strigified_payload && !$ret['payload']) {
      $ret['payload_errors'][] = 'The stringified payload is not empty, but we cannot decode it. Perhaps it is invalid JSON? It is: ' . $strigified_payload;
    }

    return $ret;
  }

  /**
   * Get all available webhooks.
   *
   * If there are none, enable webhook_receiver_example or your own modules.
   *
   * @return array
   *   Array of webhooks keyed by plugin ID.
   */
  public function webhooks() : array {
    $definitions = $this->plugins()->pluginDefinitions();

    $ret = [];

    foreach ($definitions as $definition) {
      if (array_key_exists('hide', $definition) && $definition['hide']) {
        continue;
      }
      $id = $definition['id'];
      $token = $this->webhookReceiverSecurity->token($id);

      $ret[$id] = [
        'plugin_id' => $id,
        'token' => $token,
        'webhook_path' => '/' . $id . '/' . $token,
      ];
    }

    return $ret;
  }

}
