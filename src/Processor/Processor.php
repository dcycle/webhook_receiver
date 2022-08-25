<?php

namespace Drupal\webhook_receiver\Processor;

use Drupal\webhook_receiver\WebhookReceiver;
use Drupal\webhook_receiver\Payload\PayloadInterface;
use Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface;
use Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLog;
use Drupal\webhook_receiver\WebhookReceiverActivityLog\WebhookReceiverActivityLogInterface;
use Drupal\Component\Datetime\TimeInterface;

/**
 * A processor of arbitrary webhook requests.
 */
class Processor implements ProcessorInterface {

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
   * The injected time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Class constructor.
   *
   * @param \Drupal\webhook_receiver\WebhookReceiverActivityLog\WebhookReceiverActivityLogInterface $activityLogger
   *   The injected activity logger.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The injected time service.
   */
  public function __construct(WebhookReceiverActivityLogInterface $activityLogger, TimeInterface $time) {
    $this->activityLogger = $activityLogger;
    $this->time = $time;
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
  public function process(WebhookReceiver $app, string $plugin_id, string $token, bool $simulate, PayloadInterface $payload) : array {
    $log = $this->log();
    $plugin_id = trim($plugin_id);

    try {
      $ret = [
        'code' => self::NOT_FOUND,
        'time' => $this->time->getRequestTime(),
        'log' => [
          'errors' => [],
          'debug' => [],
        ],
      ];

      // If the payload has errors, fail fast.
      $num_errors = $payload->numErrors();

      if ($num_errors) {
        $ret['log']['errors'] = $payload->errorsAsArray();
        $ret['code'] = self::BAD_REQUEST;
        return $ret;
      }

      $webhooks = $app->webhooks();
      if (array_key_exists($plugin_id, $webhooks)) {
        $log->debug($plugin_id . ' exists, processing.');
        $this->processExisting($app, $plugin_id, $token, $ret, $log, $simulate, $payload);
      }
      else {
        $plugins = count($webhooks) ? 'Plugins are ' . implode(', ', array_keys($webhooks)) : 'No plugins were found';

        $log->debug($plugin_id . ' not found in list in webhook plugins. ' . $plugins . '.');
      }

    }
    catch (\Throwable $t) {
      $ret['code'] = self::ERROR;
      $ret['access'] = FALSE;
      $ret['log']['errors'][] = [
        'message' => 'An error occurred during processing. Find the following id in the Drupal logs for details',
        'id' => $this->activityLogger->logThrowable($t),
      ];
      return $ret;
    }

    $ret['log'] = $log->toArray();

    return $ret;
  }

  /**
   * Process an existing plugin for which we do not know if it can be accessed.
   *
   * @param \Drupal\webhook_receiver\WebhookReceiver $app
   *   The module singleton.
   * @param string $plugin_id
   *   The plugin ID which will process the request.
   * @param string $token
   *   The unencrypted token.
   * @param array $ret
   *   A return array which will be stringified and client-facing.
   * @param \Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface $log
   *   A client-facing log.
   * @param bool $simulate
   *   Whether or not to simulate the action.
   * @param \Drupal\webhook_receiver\Payload\PayloadInterface $payload
   *   An array with the "payload" key, and keys for the payload errors and
   *   debug messages.
   */
  public function processExisting(WebhookReceiver $app, string $plugin_id, string $token, array &$ret, WebhookReceiverLogInterface $log, bool $simulate, PayloadInterface $payload) {

    $log->debug($plugin_id . ' exists, moving on.');
    $log->debug('Access set to FALSE unless a plugin determines safe acess.');

    $ret['access'] = FALSE;

    $log->debug('Continue is TRUE unless a plugin determines otherwise, for example for deferred execution. If continue is TRUE and access is FALSE, execution stops.');

    $ret['continue'] = TRUE;

    $log->debug('Running all alter plugins.');

    $app->plugins()->before($app, $plugin_id, $token, $ret, $log, $payload, $simulate);

    if (!$ret['access']) {
      $log->debug('Acces is denied to the webhook.');
      $ret['code'] = self::FORBIDDEN;
      return $ret;
    }
    if ($ret['continue']) {
      $this->processNow($app, $plugin_id, $log, $simulate, $payload, $ret);
    }
  }

  /**
   * Validate and, if validated, process the request.
   *
   * @param \Drupal\webhook_receiver\WebhookReceiver $app
   *   The module singleton.
   * @param string $plugin_id
   *   The plugin ID which will process the request.
   * @param \Drupal\webhook_receiver\WebhookReceiverLog\WebhookReceiverLogInterface $log
   *   A client-facing log.
   * @param bool $simulate
   *   Whether or not to simulate the action.
   * @param \Drupal\webhook_receiver\Payload\PayloadInterface $payload
   *   An array with the "payload" key, and keys for the payload errors and
   *   debug messages.
   * @param array $ret
   *   A return array which will be stringified and client-facing.
   */
  public function processNow(WebhookReceiver $app, string $plugin_id, WebhookReceiverLogInterface $log, bool $simulate, PayloadInterface $payload, array &$ret) {
    if ($app->plugins()->byId($plugin_id)->validatePayload($payload, $log)) {
      $log->debug('Payload is valid. Moving on to process request.');
      $app->plugins()
        ->byId($plugin_id)
        ->processPayload($payload, $log, $simulate);
      $ret['code'] = self::OK;
    }
    else {
      $ret['code'] = self::BAD_REQUEST;
      $log->err('Payload has been determined to be invalid.');
    }
  }

}
