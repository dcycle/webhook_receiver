<?php

namespace Drupal\webhook_receiver;

use Drupal\webhook_receiver\Utilities\Singleton;
use Symfony\Component\HttpFoundation\Request;
use Drupal\webhook_receiver\WebhookReceiver;

/**
 * Abstraction around a collection of plugins.
 */
class WebhookReceiverPluginCollection implements WebhookReceiverPluginInterface {

  use Singleton;

  /**
   * Mockable wrapper for \Drupal::service('plugin.manager.webhook_receiver').
   *
   * @return mixed
   *   The WebhookReceiverPluginManager service. We are not specifying its type
   *   here because during testing we want to mock pluginManager() without
   *   extending WebhookReceiverPluginManager; when we do, it works fine in
   *   PHPUnit directly. However when attempting to run within Drupal we
   *   get an unhelpful message as described in
   *   https://drupal.stackexchange.com/questions/252930. Therefore we simply
   *   use an anonymous class.
   *
   * @throws \Exception
   */
  public function pluginManager() {
    return \Drupal::service('plugin.manager.webhook_receiver');
  }

  /**
   * Get plugin objects.
   *
   * @param bool $reset
   *   Whether to re-fetch plugins; otherwise we use the static variable.
   *   This can be useful during testing.
   *
   * @return array
   *   Array of plugin objects.
   *
   * @throws \Exception
   */
  public function plugins(bool $reset = FALSE) : array {
    static $return = NULL;

    if ($return === NULL || $reset) {
      $return = [];
      foreach (array_keys($this->pluginDefinitions()) as $plugin_id) {
        $return[$plugin_id] = $this->byId($plugin_id);
      }
    }

    return $return;
  }

  /**
   * Get plugin definitions based on their annotations.
   *
   * @return array
   *   Array of plugin definitions.
   *
   * @throws \Exception
   */
  public function pluginDefinitions() : array {
    $return = $this->pluginManager()->getDefinitions();

    uasort($return, function (array $a, array $b) : int {
      if ($a['weight'] == $b['weight']) {
          return 0;
      }
      return ($a['weight'] < $b['weight']) ? -1 : 1;
    });

    return $return;
  }

  /**
   * Get an array of example URLs for usage.
   *
   * @param string $base_url
   *   The base URL to use for the examples.
   * @param string $token
   *   A token which should be used for the examples.
   *
   * @return array
   *   Array of example URLs for usage.
   *
   * @throws \Exception
   */
  public function exampleUrls(string $base_url, string $token) : array {
    $return = [];

    foreach ($this->pluginDefinitions() as $pluginDefinition) {
      foreach ($pluginDefinition['examples'] as $example) {
        $return[] = str_replace('[url]', $base_url, str_replace('[token]', $token, $example));
      }
    }

    return $return;
  }

  /**
   * Get a single plugin by its id.
   *
   * @param string $plugin_id
   *   The plugin id.
   *
   * @return \Drupal\webhook_receiver\WebhookReceiverPluginInterface
   *   The plugin.
   */
  public function byId(string $plugin_id) : WebhookReceiverPluginInterface {
    return $this->pluginManager()->createInstance($plugin_id, ['of' => 'configuration values']);
  }

  /**
   * {@inheritdoc}
   */
  public function alterResponse(Request $request, array $result, array &$response) {
    foreach ($this->plugins() as $plugin) {
      $plugin->alterResponse($request, $result, $response);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function before(WebhookReceiver $app, string $plugin_id, string $token, &$ret, $log) {
    foreach ($this->plugins() as $plugin) {
      $plugin->before($app, $plugin_id, $token, $ret, $log);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function alterResult(Request $request, array &$result) {
    foreach ($this->plugins() as $plugin) {
      $plugin->alterResult($request, $result);
    }
  }

}
