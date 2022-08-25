<?php

namespace Drupal\webhook_receiver_defer\SelfTest;

use Drupal\webhook_receiver_defer\WebhookReceiverDefer;
use Drupal\webhook_receiver\SelfTest\SelfTestLogTrait;
use Drupal\webhook_receiver\Payload\PayloadFactoryInterface;

/**
 * Run self-tests on the module. These are destructive.
 *
 * Use \Drupal::service('webhook_receiver_defer.selftest').
 */
class DeferSelfTest {

  const PLUGIN_ID = 'webhook_receiver_example_log_payload';

  use SelfTestLogTrait;

  /**
   * The injected webhook receiver service.
   *
   * @var \Drupal\webhook_receiver_defer\WebhookReceiverDefer
   */
  protected $module;

  /**
   * The injected payload factory.
   *
   * @var \Drupal\webhook_receiver\Payload\PayloadFactoryInterface
   */
  protected $payloadFactory;

  /**
   * Constructor.
   *
   * @param \Drupal\webhook_receiver_defer\WebhookReceiverDefer $module
   *   The defer module singleton.
   * @param \Drupal\webhook_receiver\Payload\PayloadFactoryInterface $payloadFactory
   *   The payload factory singleton.
   */
  public function __construct(WebhookReceiverDefer $module, PayloadFactoryInterface $payloadFactory) {
    $this->module = $module;
    $this->payloadFactory = $payloadFactory;
  }

  /**
   * Run the destructive test.
   */
  public function run() {
    $this->log('Starting webhook_receiver_defer self-test.');
    $this->setup();
    $this->assertByType('new', 4);
    $this->log('Running cron.');
    $this->module->hookCron();
    $this->log('webhook_receiver_defer all tests complete!');
  }

  /**
   * Make sure the database contains the right number of items of a type.
   *
   * @param string $type
   *   A type, for example new, or 200, or 500.
   * @param int $count
   *   The expected number of items of this type.
   */
  public function assertByType(string $type, int $count) {
    $byType = $this->module->countByType();

    foreach ($byType as $row) {
      if ($row->status == $type) {
        if ($row->count == $count) {
          $this->log('Asserting we have ' . $count . ' ' . $type . ' records');
          return;
        }
        $this->err('We were looking for ' . $count . ' ' . $type . ' records, but found ' . $row->count);
      }
    }

    $this->err('We were looking for ' . $count . ' ' . $type . ' records, but found none');
  }

  /**
   * Perform inital setup for the destructive test.
   */
  public function setup() {
    $this->remember('{"This must be set!": "This should be deferred"}');
    $this->remember('{"This must be set!": "So should this"}');
    $this->remember('{"This must be set!": "This should be deferred"}', simulate: TRUE);
    $this->remember('{"This should fail": "because of a missing This must be set! key"}');
  }

  /**
   * Tell the deferral system to remember a request.
   *
   * @param string $payload_string
   *   The payload, as a string.
   * @param bool $simulate
   *   Whether to simulate the result or not.
   */
  public function remember(string $payload_string, bool $simulate = FALSE) {
    $this->module->rememberRequest(self::PLUGIN_ID, $this->payloadFactory->fromString($payload_string), $simulate);
  }

}
