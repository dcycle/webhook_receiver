<?php

namespace Drupal\webhook_receiver\WebhookReceiverActivityLog;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Utility\Error;

/**
 * A log whose results are logged to the watchdog.
 */
class WebhookReceiverActivityLog implements WebhookReceiverActivityLogInterface {

  const LOG_CHANNEL = 'webhook_receiver';

  /**
   * The injected logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The injected UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The injected logger factory.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   The injected UUID service.
   */
  public function __construct(LoggerChannelFactoryInterface $loggerFactory, UuidInterface $uuid) {
    $this->loggerFactory = $loggerFactory;
    $this->uuid = $uuid;
  }

  /**
   * {@inheritdoc}
   */
  public function logThrowable(\Throwable $throwable) : string {
    $uuid = $this->uuid->generate();

    $variables = Error::decodeException($throwable);

    $this->loggerFactory->get(self::LOG_CHANNEL)->error('%type @uuid: @message in %function (line %line of %file).', array_merge($variables, [
      '@uuid' => $uuid,
    ]));

    return $uuid;
  }

}
