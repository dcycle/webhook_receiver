<?php

namespace Drupal\webhook_receiver;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\State\State;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\system\SystemManager;
use Symfony\Component\HttpFoundation\Request;
use Drupal\webhook_receiver\WebhookReceiverSecurity\WebhookReceiverSecurity;
use Drupal\webhook_receiver\WebhookReceiverProcessor\WebhookReceiverProcessorInterface;

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
   * @param array $payload
   *   An array with the keys: 'payload' for the payload itself, and
   *   'payload_errors' for any errors which should trigger a 500 error,
   *   'payload_notices' for any notices which have no effect on the response
   *   code.
   *
   * @return array
   *   A response for output as JSON.
   */
  public function process(string $plugin_id, string $token, bool $simulate, array $payload) : array {
    return $this->processor->process($this, $plugin_id, $token, $simulate, $payload);
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
