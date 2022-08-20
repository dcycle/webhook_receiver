<?php

namespace Drupal\webhook_receiver\WebhookReceiverProcessor;

use Drupal\webhook_receiver\WebhookReceiver;
use Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface;
use Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLog;
use Drupal\webhook_receiver\WebhookReceiverActivityLog\WebhookReceiverActivityLogInterface;

class WebhookReceiverProcessor implements WebhookReceiverProcessorInterface {

  const FORBIDDEN = 403;
  const NOT_FOUND = 404;
  const ERROR = 500;
  const OK = 200;
  const BAD_REQUEST = 400;

  /**
   * The injected activity logger.
   *
   * @var \Drupal\webhook_receiver\WebhookReceiverActivityLog\WebhookReceiverActivityLogInterface
   */
  protected $activityLogger;

  /**
   * Class constructor.
   *
   * @param \Drupal\webhook_receiver\WebhookReceiverActivityLog\WebhookReceiverActivityLogInterface $activityLogger
   *   The injected activity logger.
   */
  public function __construct(WebhookReceiverActivityLogInterface $activityLogger) {
    $this->activityLogger = $activityLogger;
  }

  /**
   * Get a response logger.
   *
   * @return \Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface
   *   A response logger.
   */
  public function log() : WebhookReceiverLogInterface {
    return new WebhookReceiverLog();
  }

  /**
   * {@inheritdoc}
   */
  public function process(WebhookReceiver $app, string $plugin_id, string $token, bool $simulate, array $payload) : array {
    $log = $this->log();

    try {
      $ret = [
        'code' => self::NOT_FOUND,
        'time' => rand(),
        'log' => [
          'errors' => [],
          'debug' => [],
        ],
      ];

      // If the payload has errors, fail fast.
      if (count($payload['payload_errors'])) {
        $ret['log']['errors'] = $payload['payload_errors'];
        $ret['code'] = self::BAD_REQUEST;
        return $ret;
      }

      if (array_key_exists($plugin_id, $app->webhooks())) {
        $log->debug($plugin_id . ' exists, processing.');
        $this->processExisting($app, $plugin_id, $token, $ret, $log, $simulate, $payload);
      }
      else {
        $log->debug($plugin_id . ' not found in list in webhook plugins.');
      }

    }
    catch (\Throwable $t) {
      $ret['code'] = self::ERROR;
      $ret['log']['errors'][] = [
        'message' => 'An error occurred during processing. Find the following id in the Drupal logs for details',
        'id' => $this->activityLogger->logThrowable($t),
      ];
      return $ret;
    }

    $ret['log'] = $log->toArray();

    if (count($ret['log']['errors'])) {
      $ret['code'] = self::ERROR;
    }

    return $ret;
  }

  public function processExisting(WebhookReceiver $app, string $plugin_id, string $token, &$ret, $log, $simulate, $payload) {

    $log->debug($plugin_id . ' exists, moving on.');
    $log->debug('Access set to FALSE unless a plugin determines safe acess.');

    $ret['access'] = FALSE;

    $log->debug('Continue is TRUE unless a plugin determines otherwise, for example for deferred execution. If continue is TRUE and access is FALSE, execution stops.');

    $ret['continue'] = TRUE;

    $log->debug('Running all alter plugins.');

    $app->plugins()->before($app, $plugin_id, $token, $ret, $log);

    if (!$ret['access']) {
      $log->debug('Acces is denied to the webhook.');
      $ret['code'] = self::FORBIDDEN;
      return $ret;
    }
    if ($ret['continue']) {
      $this->processNow($app, $plugin_id, $log, $simulate, $payload, $ret);
    }
  }

  public function processNow($app, $plugin_id, $log, $simulate, $payload, array &$ret) {
    if ($app->plugins()->byId($plugin_id)->validatePayloadArray($payload['payload'], $log, $simulate)) {
      $log->debug('Payload is valid. Moving on to process request.');
      $app->plugins()->byId($plugin_id)->processPayloadArray($payload['payload'], $log, $simulate);
      $ret['code'] = self::OK;
    }
    else {
      $ret['code'] = self::BAD_REQUEST;
      $log->err('Payload has been determined to be invalid.');
    }
  }

}
