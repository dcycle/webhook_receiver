<?php

namespace Drupal\webhook_receiver_defer;

use Drupal\webhook_receiver\Payload\PayloadInterface;
use Drupal\Component\Serialization\Json;
use Drupal\webhook_receiver\WebhookReceiver;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Database\Connection;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\webhook_receiver\Payload\PayloadFactoryInterface;

/**
 * WebhookReceiverDefer singleton.
 *
 * Use \Drupal::service('webhook_receiver_defer').
 */
class WebhookReceiverDefer {

  const TABLE = 'webhook_receiver_defer';
  const MAX_CRON_EXECUTION = 120;

  /**
   * The injected webhook receiver service.
   *
   * @var \Drupal\webhook_receiver\WebhookReceiver
   */
  protected $webhookReceiver;

  /**
   * The injected UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The injected time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The injected payload factory.
   *
   * @var \Drupal\webhook_receiver\Payload\PayloadFactoryInterface
   */
  protected $payloadFactory;

  /**
   * Constructs a new WebhookReceiverDefer object.
   *
   * @param \Drupal\webhook_receiver\WebhookReceiver $webhookReceiver
   *   An injected webhook receiver module.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   *   The injected UUID service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The injected database connection.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The injected time.
   * @param \Drupal\webhook_receiver\Payload\PayloadFactoryInterface $payloadFactory
   *   The injected payload factory.
   */
  public function __construct(WebhookReceiver $webhookReceiver, UuidInterface $uuid, Connection $connection, TimeInterface $time, PayloadFactoryInterface $payloadFactory) {
    $this->webhookReceiver = $webhookReceiver;
    $this->uuid = $uuid;
    $this->connection = $connection;
    $this->time = $time;
    $this->payloadFactory = $payloadFactory;
  }

  /**
   * Remember a request for future processing.
   *
   * @param string $plugin_id
   *   The plugin ID.
   * @param \Drupal\webhook_receiver\Payload\PayloadInterface $payload
   *   The payload.
   * @param bool $simulate
   *   Wheter to simulate the request.
   */
  public function rememberRequest(string $plugin_id, PayloadInterface $payload, bool $simulate) : string {
    $uuid = $this->uuid->generate();

    $this->connection->insert(self::TABLE)
      ->fields([
        'uuid' => $uuid,
        'status' => 'new',
        'plugin_id' => trim($plugin_id),
        'payload' => $payload->asString(),
        'time' => $this->time->getRequestTime(),
        'simulate' => $simulate ? 1 : 0,
      ])
      ->execute();

    return $uuid;
  }

  /**
   * Testable implementation of hook_cron().
   */
  public function hookCron() {
    $start = $this->time->getCurrentTime();
    while ($this->time->getCurrentTime() - $start <= self::MAX_CRON_EXECUTION) {
      if (!$this->processNext()) {
        break;
      }
    }
  }

  /**
   * Process the next new item.
   *
   * @return bool
   *   TRUE if an item existed to be processed.
   */
  public function processNext() : bool {
    if ($next = $this->getSingle()) {
      $uuid = $next[0]->uuid;
      $plugin_id = $next[0]->plugin_id;
      $token = $this->webhookReceiver->webhookReceiverSecurity()->token($plugin_id);
      $simulate = $next[0]->simulate;
      $payload = $this->payloadFactory->fromString($next[0]->payload);

      // Set no-defer to avoid infinite loops.
      $payload_array = $payload->toArray();
      $payload_array['no-defer'] = TRUE;
      $payload->fromArray($payload_array);

      $result = $this->webhookReceiver->process($plugin_id, $token, $simulate, $payload);

      $this->setProcessed($uuid, $result['code'], $result);

      return TRUE;
    }
    return FALSE;
  }

  /**
   * Set a request as processed, with a response code and result.
   *
   * @param string $uuid
   *   A request to be set as processed.
   * @param string $code
   *   A result code, for example 500 or 200.
   * @param array $result
   *   The complete result.
   */
  public function setProcessed(string $uuid, string $code, array $result) {
    $this->connection->update(self::TABLE)
      ->fields([
        'status' => $code,
        'response' => JSON::encode($result),
      ])
      ->condition('uuid', $uuid)
      ->execute();
  }

  /**
   * Get a single new request, if possible.
   *
   * @return array
   *   An empty array if there are no new requests, otherwisee the array will
   *   contain a single item: a request in the form of a struct.
   */
  public function getSingle() : array {
    $query = $this->connection->select(self::TABLE, 'requests');
    $query->condition('status', 'new');
    $query->addField('requests', 'uuid');
    $query->addField('requests', 'plugin_id');
    $query->addField('requests', 'payload');
    $query->addField('requests', 'simulate');
    $query->range(0, 1);
    return $query->execute()->fetchAll();
  }

  /**
   * Count all items in the queue, by type.
   *
   * @return array
   *   Array of objects, each object having status (e.g. new) and count (int).
   */
  public function countByType() {
    $query = $this->connection->select(self::TABLE, 'requests');
    $query->addField('requests', 'status');
    $query->addExpression('COUNT(requests.status)', 'count');
    $query->groupBy('requests.status');
    return $query->execute()->fetchAll();
  }

}
