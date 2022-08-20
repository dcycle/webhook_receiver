<?php

namespace Drupal\webhook_receiver;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\webhook_receiver\Annotation\WebhookReceiverPluginAnnotation;

/**
 * A plugin manager for WebhookReceiverAlterPlugin plugins.
 *
 * The WebhookReceiverPluginManager class extends the DefaultPluginManager to
 * provide a way to manage WebhookReceiverAlterPlugin plugins. A plugin manager
 * defines a new plugin type and how instances of any plugin of that type will
 * be discovered, instantiated and more.
 *
 * Using the DefaultPluginManager as a starting point sets up our
 * WebhookReceiverAlterPlugin plugin type to use annotated discovery.
 *
 * The plugin manager is also declared as a service in
 * webhook_receiver.services.yml so that it can be easily accessed and used
 * anytime we need to work with WebhookReceiverAlterPlugin plugins.
 *
 * This is based on code from the Examples module which can be found at
 * https://github.com/drupalprojects/examples/blob/8.x-1.x/plugin_type_example/src/SandwichPluginManager.php
 */
// See https://github.com/mglaman/phpstan-drupal/issues/113
// @codingStandardsIgnoreStart
class WebhookReceiverAlterPluginManager extends DefaultPluginManager {
// @codingStandardsIgnoreEnd

  /**
   * Creates the discovery object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  // See https://github.com/mglaman/phpstan-drupal/issues/112
  // @codingStandardsIgnoreStart
  // @phpstan:ignoreError
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
  // @codingStandardsIgnoreEnd
    // We replace the $subdir parameter with our own value.
    // This tells the plugin manager to look for plugins in the
    // 'src/Plugin/WebhookReceiverAlterPlugin' subdirectory of any enabled
    // modules. This also serves to define the PSR-4 subnamespace in which
    // plugins will live. Modules can put a plugin class in their own namespace
    // such as Drupal\{module_name}\Plugin\WebhookReceiverAlterPlugin\MyPlugin.
    $subdir = 'Plugin/WebhookReceiverAlterPlugin';

    // The name of the interface that plugins should adhere to. Drupal will
    // enforce this as a requirement. If a plugin does not implement this
    // interface, Drupal will throw an error.
    $plugin_interface = WebhookReceiverPluginInterface::class;

    // The name of the annotation class that contains the plugin definition.
    $plugin_definition_annotation_name = WebhookReceiverPluginAnnotation::class;

    parent::__construct($subdir, $namespaces, $module_handler, $plugin_interface, $plugin_definition_annotation_name);

    // This sets the caching method for our plugin definitions. Plugin
    // definitions are discovered by examining the $subdir defined above, for
    // any classes with an $plugin_definition_annotation_name. The annotations
    // are read, and then the resulting data is cached using the provided cache
    // backend. For our WebhookReceiverAlterPlugin plugin type, we've specified
    // the "@cache.default" service be used in the
    // plugin_type_example.services.yml file (*) but see
    // https://www.drupal.org/project/examples/issues/3109867. The second
    // argument is a cache key prefix. Out of the box Drupal with the default
    // cache backend setup will store our plugin definition in the
    // cache_default table using the webhook_receiver_plugin key. All that is
    // implementation details however, all we care about it that caching for
    // our plugin definition is taken care of by this call.
    $this->setCacheBackend($cache_backend, 'webhook_receiver_plugin');
  }

}
