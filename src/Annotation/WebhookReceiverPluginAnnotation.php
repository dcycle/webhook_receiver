<?php

namespace Drupal\webhook_receiver\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an Expose Status Plugin annotation object.
 *
 * See the plugin_type_example module of the examples module for how this works.
 *
 * @see http://drupal.org/project/examples
 * @see \Drupal\webhook_receiver\WebhookReceiverPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class WebhookReceiverPluginAnnotation extends Plugin {

  /**
   * A brief, human readable, description of the modifier.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * Examples of how to use the plugin.
   *
   * @var array
   */
  public $examples;

  /**
   * How this modifier should be ordered.
   *
   * @var float
   */
  public $weight;

}
