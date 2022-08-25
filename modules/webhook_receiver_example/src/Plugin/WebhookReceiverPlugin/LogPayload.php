<?php

namespace Drupal\webhook_receiver_example\Plugin\WebhookReceiverPlugin;

use Drupal\webhook_receiver\WebhookReceiverPluginBase;
use Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\webhook_receiver\Payload\PayloadInterface;

/**
 * Very simple example of the webhook receiver module which logs the payload.
 *
 * @WebhookReceiverPluginAnnotation(
 *   id = "webhook_receiver_example_log_payload",
 *   description = @Translation("Log the payload of a webhook."),
 *   weight = 0,
 *   examples = {
 *   },
 * )
 */
class LogPayload extends WebhookReceiverPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The injected logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  const PAYLOAD_REQUIRED_KEY = 'This must be set!';
  const VALUE_TO_SIMULATE_EXCEPTION_ON_VALIDATE = 'Simulate internal exception on validate.';
  const VALUE_TO_SIMULATE_EXCEPTION_ON_PROCESS = 'Simulate internal exception on process.';

  /**
   * Class constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->loggerFactory = $logger_factory;
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
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validatePayload(PayloadInterface $payload, WebhookReceiverLogInterface $log) : bool {
    // This demonstrates how you might validate that a payload is actually
    // usable by your plugin. If this passes, then the ::processPayloadArray()
    // method is called.
    if (!$payload->validatePath([self::PAYLOAD_REQUIRED_KEY], '', function ($x) {
      return $x ? TRUE : FALSE;
    })) {
      $log->err('We are expecting the payload to contain the key "' . self::PAYLOAD_REQUIRED_KEY . '" and for it to be a non-empty string.');
      return FALSE;
    }
    if ($payload->getString([self::PAYLOAD_REQUIRED_KEY]) == self::VALUE_TO_SIMULATE_EXCEPTION_ON_VALIDATE) {
      throw new \Exception('Simulating exception because the value of key ' . self::PAYLOAD_REQUIRED_KEY . ' is ' . self::VALUE_TO_SIMULATE_EXCEPTION_ON_VALIDATE);
    }
    $log->debug('The payload is valid.');
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function processPayloadArray(PayloadInterface $payload, WebhookReceiverLogInterface $log, bool $simulate) {
    if ($payload[self::PAYLOAD_REQUIRED_KEY] == self::VALUE_TO_SIMULATE_EXCEPTION_ON_PROCESS) {
      throw new \Exception('Simulating exception because the value of key ' . self::PAYLOAD_REQUIRED_KEY . ' is ' . self::VALUE_TO_SIMULATE_EXCEPTION_ON_PROCESS);
    }

    $notice = "The payload's '" . self::PAYLOAD_REQUIRED_KEY . "' key is: " . $payload[self::PAYLOAD_REQUIRED_KEY];

    if ($simulate) {
      $log->debug('We are simulating the action so we are not actually logging anything in the watchdog. If we were not simulating we would log:');
      $log->debug($notice);
    }
    else {
      $this->loggerFactory->get('webhook_receiver_example')->notice($notice);
    }

    $log->debug('The payload has been logged successfully');
  }

}
