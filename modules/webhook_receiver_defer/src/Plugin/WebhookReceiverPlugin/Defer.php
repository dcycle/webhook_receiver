<?php

namespace Drupal\webhook_receiver_defer\Plugin\WebhookReceiverPlugin;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\webhook_receiver\Payload\PayloadInterface;
use Drupal\webhook_receiver\WebhookReceiver;
use Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface;
use Drupal\webhook_receiver\WebhookReceiverPluginBase;
use Drupal\webhook_receiver_defer\WebhookReceiverDefer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defer processing of webhooks.
 *
 * @WebhookReceiverPluginAnnotation(
 *   id = "webhook_receiver_defer",
 *   hide = true,
 *   description = @Translation("Defer processing of webhooks."),
 *   weight = -50,
 *   examples = {
 *   },
 * )
 */
class Defer extends WebhookReceiverPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The injected webhook_receiver_defer service.
   *
   * @var \Drupal\webhook_receiver_defer\WebhookReceiverDefer
   */
  protected $webhookReceiverDefer;

  /**
   * Class constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, WebhookReceiverDefer $webhookReceiverDefer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->webhookReceiverDefer = $webhookReceiverDefer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    // PHPStan complains that using this should make the class final, but
    // this is widely used in Drupal, for example in
    // ./core/lib/Drupal/Core/Entity/Controller/EntityController.php.
    // Declaring the class final would make it unmockable.
    // @phpstan-ignore-next-line
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('webhook_receiver_defer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function before(WebhookReceiver $app, string $plugin_id, string $token, array &$ret, WebhookReceiverLogInterface $log, PayloadInterface $payload, bool $simulate) {
    if ($payload->getBool(['no-defer'], default: FALSE) == TRUE) {
      // Just ignore the request to defer. Do nothing. This might be useful
      // for request-response tests which require immediate responses and the
      // exact response we are anticipating. Using the no-defer flag during
      // testing, for example at
      // ./modules/webhook_receiver_example/request-response-test/01/request.json,
      // allows these tests to work whether or not webhook_receiver_defer is
      // active.
      // This also avoids infinite loops, as when requests are processed during
      // cron, we set 'no-defer' on the payload.
      $payload->unset(['no-defer']);
      return;
    }

    if (!$ret['access']) {
      $log->debug('Access is denied; we will not defer execution');
      return;
    }

    $log->debug('Remembering the request for deferred processing');
    $log->debug($this->webhookReceiverDefer->rememberRequest($plugin_id, $payload, $simulate));

    $ret['code'] = 200;
    $ret['continue'] = FALSE;
  }

}
