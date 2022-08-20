<?php

namespace Drupal\webhook_receiver\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\webhook_receiver\WebhookReceiver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\webhook_receiver\Utilities\Mockables;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Component\Serialization\Json;

/**
 * Controller for the /admin/reports/status/[token] request.
 */
class WebhookReceiverController extends ControllerBase {

  use Mockables;

  /**
   * The injected webhook_receiver service.
   *
   * @var \Drupal\webhook_receiver\WebhookReceiver
   */
  protected $webhookReceiver;

  /**
   * The injected request stack, to get POST data.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new WebhookReceiverController object.
   *
   * @param \Drupal\webhook_receiver\WebhookReceiver $webhook_receiver
   *   An injected webhook_receiver service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   An injected request stack.
   */
  public function __construct(WebhookReceiver $webhook_receiver, RequestStack $request_stack) {
    $this->webhookReceiver = $webhook_receiver;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // PHPStan complains that using this should make the class final, but
    // this is widely used in Drupal, for example in
    // ./core/lib/Drupal/Core/Entity/Controller/EntityController.php.
    // Declaring the class final would make it unmockable.
    // @phpstan-ignore-next-line
    return new static(
      $container->get('webhook_receiver'),
      $container->get('request_stack'),
    );
  }

  /**
   * Process a request.
   *
   * @param string $plugin_id
   *   A plugin ID.
   * @param string $token
   *   A token.
   * @param bool $simulate
   *   Whether to simulate the processing of a request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A response for output as JSON.
   */
  public function process(string $plugin_id, string $token, bool $simulate = FALSE) {
    $data = $this->webhookReceiver->process($plugin_id, $token, $simulate, $this->payload());
    $ret = new JsonResponse($data);
    if (!empty($data['code'])) {
      $ret->setStatusCode($data['code']);
    }
    return $ret;
  }

  /**
   * Get the JSON payload.
   *
   * @return array
   *   An array with the keys: 'payload' for the payload itself, and
   *   'payload_errors' for any errors which should trigger a 500 error,
   *   'payload_notices' for any notices which have no effect on the response
   *   code.
   */
  public function payload() : array {
    $ret = [
      'payload_errors' => [],
      'payload_notices' => [],
    ];

    $strigified_payload = $this->requestStack->getCurrentRequest()->getContent();

    if (!$strigified_payload) {
      $ret['payload_notices'][] = 'The stringified payload computes as empty';
    }

    $ret['payload'] = Json::decode($strigified_payload);

    if ($strigified_payload && !$ret['payload']) {
      $ret['payload_errors'][] = 'The stringified payload is not empty, but we cannot decode it. Perhaps it is invalid JSON? It is: ' . $strigified_payload;
    }

    return $ret;
  }

  /**
   * Simulate the processing of a request.
   *
   * @param string $plugin_id
   *   A plugin ID.
   * @param string $token
   *   A token.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   A response for output as JSON.
   */
  public function simulate(string $plugin_id, string $token) {
    return $this->process($plugin_id, $token, simulate: TRUE);
  }

}
