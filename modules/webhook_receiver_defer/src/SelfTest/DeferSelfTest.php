<?php

namespace Drupal\webhook_receiver_defer\SelfTest;

use Drupal\Core\Database\Connection;
use Drupal\webhook_receiver\Payload\PayloadFactoryInterface;
use Drupal\webhook_receiver\SelfTest\SelfTestLogTrait;
use Drupal\webhook_receiver_defer\WebhookReceiverDefer;

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
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructor.
   *
   * @param \Drupal\webhook_receiver_defer\WebhookReceiverDefer $module
   *   The defer module singleton.
   * @param \Drupal\webhook_receiver\Payload\PayloadFactoryInterface $payloadFactory
   *   The payload factory singleton.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(WebhookReceiverDefer $module, PayloadFactoryInterface $payloadFactory, Connection $connection) {
    $this->module = $module;
    $this->payloadFactory = $payloadFactory;
    $this->connection = $connection;
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
    $this->assertByType('400', 1);
    $this->assertByType('200', 3);
    $this->assertByType('200', 3);
    $this->assertMessageCount(2);
    $this->assertMessage("The payload's 'This must be set!' key is: This should be deferred");
    $this->assertMessage("The payload's 'This must be set!' key is: So should this");
  }

  /**
   * Make sure a given number of log messages are in the database.
   *
   * @param int $expected
   *   Number of log messages we are expecting.
   */
  public function assertMessageCount(int $expected) {
    $query = $this->connection->select('watchdog', 'watchdog');
    $query->addField('watchdog', 'type');
    $query->condition('type', 'webhook_receiver_example');
    $query->addExpression('COUNT(watchdog.type)', 'count');
    $count = $query->execute()->fetchAll()[0]->count;
    if ($count == $expected) {
      $this->log('We have ' . $expected . ' watchdog messages as expected.');
    }
    else {
      $this->err('We were expecting ' . $expected . ' watchdog messages but found ' . $count . '.');
    }
  }

  /**
   * Make sure a message exists in the watchdog.
   *
   * @param string $expected
   *   A message we are expecting to exist in the database.
   */
  public function assertMessage(string $expected) {
    $query = $this->connection->select('watchdog', 'watchdog');
    $query->addField('watchdog', 'type');
    $query->addField('watchdog', 'message');
    $query->condition('type', 'webhook_receiver_example');
    $query->condition('message', $expected);
    $all = $query->execute()->fetchAll();
    if (count($all) == 1) {
      $this->log('We have found the expected message ' . $expected);
    }
    else {
      $this->err('The message ' . $expected . ' does not appear exactly once.');
    }
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
          $this->log('Asserting we have ' . $count . ' ' . $type . ' record(s)');
          return;
        }
        $this->err('We were looking for ' . $count . ' ' . $type . ' record(s), but found ' . $row->count);
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
    $this->remember('{"This must be set!": "This should be deferred but not logged because it is simulated"}', simulate: TRUE);
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
